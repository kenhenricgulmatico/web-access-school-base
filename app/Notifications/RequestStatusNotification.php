<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * E-mails the requester when their Material Request or Facility
 * Reservation is approved or rejected (Objective 3 of the study).
 */
class RequestStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected string $status,        // 'approved' or 'rejected'
        protected string $approverName,  // e.g. "Juan Dela Cruz (Program Head)"
        protected string $requestKind,   // 'Facility Reservation' or 'Material Request'
        protected string $details,       // facility + date/time, or item list
        protected ?string $reason = null,
        protected ?int $requestId = null,
        protected ?string $link = null,  // optional custom "View Request" URL
    ) {
        // Only send after the DB transaction that changed the status commits.
        $this->afterCommit();
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $approved = $this->status === 'approved';

        $e       = fn (?string $v) => e((string) $v);
        $name    = $e($notifiable->name);
        $kind    = $e($this->requestKind);
        $by      = $e($this->approverName);
        $details = $e($this->details);
        $url     = $e($this->buildUrl());

        $title      = $approved ? "{$kind} Approved" : "{$kind} Rejected";
        $intro      = $approved
            ? "Hi {$name}, your request has been reviewed and approved."
            : "Hi {$name}, unfortunately your request was not approved.";
        $verb       = $approved ? 'approved' : 'rejected';
        $iconBg     = $approved ? '#ecfdf5' : '#fef2f2';
        $iconColor  = $approved ? '#1C6B45' : '#b91c1c';
        $icon       = $approved ? '&#9989;' : '&#10060;';

        $reasonBlock = (! $approved && $this->reason)
            ? '<p style="margin:0 0 24px 0;font-size:14px;line-height:1.6;color:#b91c1c;"><strong>Reason:</strong> '
                . $e($this->reason) . '</p>'
            : '';

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
                    <td style="background-color:#ffffff;border:1px solid #e5e7eb;border-radius:16px;padding:36px 32px;box-shadow:0 1px 3px rgba(0,0,0,0.06);">

                        <div style="width:48px;height:48px;background-color:{$iconBg};border-radius:9999px;text-align:center;margin-bottom:20px;">
                            <span style="color:{$iconColor};font-size:20px;line-height:48px;">{$icon}</span>
                        </div>

                        <h1 style="margin:0 0 4px 0;font-size:20px;font-weight:700;color:#111827;">{$title}</h1>
                        <p style="margin:0 0 24px 0;font-size:14px;color:#6b7280;">{$intro}</p>

                        <div style="height:1px;background-color:#f3f4f6;margin:0 0 24px 0;"></div>

                        <p style="margin:0 0 16px 0;font-size:15px;line-height:1.6;color:#374151;">
                            <strong>{$by}</strong> {$verb} your <strong>{$kind}</strong>.
                        </p>

                        <div style="background-color:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;padding:16px;margin:0 0 24px 0;">
                            <p style="margin:0;font-size:14px;line-height:1.6;color:#374151;">{$details}</p>
                        </div>

                        {$reasonBlock}

                        <table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 0 8px 0;">
                            <tr>
                                <td style="background-color:#123524;border-radius:8px;">
                                    <a href="{$url}" style="display:inline-block;padding:12px 24px;font-size:14px;font-weight:600;color:#ffffff;text-decoration:none;border-radius:8px;">
                                        View Request
                                    </a>
                                </td>
                            </tr>
                        </table>

                    </td>
                </tr>
                <tr>
                    <td style="padding-top:24px;text-align:center;">
                        <p style="margin:0;font-size:12px;color:#9ca3af;">
                            Colegio de Sta. Ana de Victorias — Resource Management System
                        </p>
                    </td>
                </tr>
            </table>
        </div>
        HTML;

        return (new MailMessage)
            ->subject("Your {$this->requestKind} has been {$verb}")
            ->view('mail.raw', ['html' => $html]);
    }

    protected function buildUrl(): string
    {
        if ($this->link) {
            return $this->link;
        }

        return $this->requestId
            ? route('portal.view-material-reservation', $this->requestId)
            : route('portal.dashboard');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'status'     => $this->status,
            'request_id' => $this->requestId,
        ];
    }
}
