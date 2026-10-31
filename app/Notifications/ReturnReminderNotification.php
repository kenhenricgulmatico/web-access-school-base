<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Reminds a borrower that items are due back (today / tomorrow) or overdue.
 * Sent by the daily `returns:send-reminders` command.
 */
class ReturnReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param array<int, array{name: string, quantity: int, due: string, label: string, overdue: bool}> $items
     */
    public function __construct(protected array $items) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $hasOverdue = collect($this->items)->contains('overdue', true);

        $rows = collect($this->items)->map(function ($i) {
            $color = $i['overdue'] ? '#b91c1c' : '#7A4D00';
            return '<tr>'
                . '<td style="padding:10px 12px;border-bottom:1px solid #f3f4f6;font-size:14px;color:#111827;">' . e($i['name']) . ' (x' . (int) $i['quantity'] . ')</td>'
                . '<td style="padding:10px 12px;border-bottom:1px solid #f3f4f6;font-size:14px;color:#374151;white-space:nowrap;">' . e($i['due']) . '</td>'
                . '<td style="padding:10px 12px;border-bottom:1px solid #f3f4f6;font-size:13px;font-weight:600;color:' . $color . ';white-space:nowrap;">' . e($i['label']) . '</td>'
                . '</tr>';
        })->implode('');

        $name  = e($notifiable->name);
        $title = $hasOverdue ? 'You have overdue items' : 'Items due for return';
        $intro = $hasOverdue
            ? "Hi {$name}, some items you borrowed are past their return date. Please return them to your Program Head as soon as possible."
            : "Hi {$name}, this is a reminder to return the items you borrowed on time.";
        $url   = e(route('portal.calendar'));

        $html = <<<HTML
        <div style="background-color:#f9fafb;padding:40px 20px;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;margin:0 auto;">
                <tr>
                    <td style="padding-bottom:24px;text-align:center;">
                        <span style="display:inline-block;background-color:#123524;color:#ffffff;font-size:12px;font-weight:600;letter-spacing:0.05em;text-transform:uppercase;padding:6px 14px;border-radius:9999px;">
                            Colegio de Sta. Ana de Victorias
                        </span>
                    </td>
                </tr>
                <tr>
                    <td style="background-color:#ffffff;border:1px solid #e5e7eb;border-radius:16px;padding:36px 32px;">
                        <h1 style="margin:0 0 8px 0;font-size:20px;font-weight:700;color:#111827;">{$title}</h1>
                        <p style="margin:0 0 24px 0;font-size:14px;line-height:1.6;color:#6b7280;">{$intro}</p>

                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #e5e7eb;border-radius:10px;border-collapse:separate;margin:0 0 24px 0;">
                            <tr>
                                <th align="left" style="padding:10px 12px;background:#f9fafb;font-size:12px;color:#6b7280;text-transform:uppercase;">Item</th>
                                <th align="left" style="padding:10px 12px;background:#f9fafb;font-size:12px;color:#6b7280;text-transform:uppercase;">Return by</th>
                                <th align="left" style="padding:10px 12px;background:#f9fafb;font-size:12px;color:#6b7280;text-transform:uppercase;">Status</th>
                            </tr>
                            {$rows}
                        </table>

                        <a href="{$url}" style="display:inline-block;padding:12px 24px;background-color:#123524;color:#ffffff;font-size:14px;font-weight:600;text-decoration:none;border-radius:8px;">
                            Open my calendar
                        </a>
                    </td>
                </tr>
                <tr>
                    <td style="padding-top:24px;text-align:center;">
                        <p style="margin:0;font-size:12px;color:#9ca3af;">Colegio de Sta. Ana de Victorias — Resource Management System</p>
                    </td>
                </tr>
            </table>
        </div>
        HTML;

        return (new MailMessage)
            ->subject($hasOverdue ? 'Reminder: you have overdue borrowed items' : 'Reminder: borrowed items are due soon')
            ->view('mail.raw', ['html' => $html]);
    }

    public function toArray(object $notifiable): array
    {
        return ['items' => $this->items];
    }
}
