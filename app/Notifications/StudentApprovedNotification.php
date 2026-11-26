<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class StudentApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected string $role = 'Faculty', // ✅ only admin, program head and faculty accounts exist
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $department = $notifiable->department?->department_name ?? 'your department';

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

                        <div style="width:48px;height:48px;background-color:#ecfdf5;border-radius:9999px;display:flex;align-items:center;justify-content:center;margin-bottom:20px;">
                            <span style="color:#059669;font-size:22px;line-height:48px;">&#10003;</span>
                        </div>

                        <h1 style="margin:0 0 4px 0;font-size:20px;font-weight:700;color:#111827;">
                            Account Approved
                        </h1>
                        <p style="margin:0 0 24px 0;font-size:14px;color:#6b7280;">
                            Hi {$notifiable->name}, your account is ready to go.
                        </p>

                        <div style="height:1px;background-color:#f3f4f6;margin:0 0 24px 0;"></div>

                        <p style="margin:0 0 16px 0;font-size:15px;line-height:1.6;color:#374151;">
                            Your account has been <strong style="color:#059669;">approved</strong>. You can now log in and access your dashboard.
                        </p>

                        <p style="margin:0 0 24px 0;font-size:15px;line-height:1.6;color:#374151;">
                            You are registered as a <strong>{$this->role}</strong> at Colegio De Sta. Ana de Victorias, under the
                            <strong>Department of {$department}</strong>.
                        </p>

                        <table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 0 8px 0;">
                            <tr>
                                <td style="background-color:#123524;border-radius:8px;">
                                    <a href="{$this->buildLoginUrl()}"
                                       style="display:inline-block;padding:12px 24px;font-size:14px;font-weight:600;color:#ffffff;text-decoration:none;border-radius:8px;">
                                        Log In to Your Account
                                    </a>
                                </td>
                            </tr>
                        </table>

                    </td>
                </tr>
                <tr>
                    <td style="padding-top:24px;text-align:center;">
                        <p style="margin:0;font-size:12px;color:#9ca3af;">
                            Thank you for being part of Colegio De Sta. Ana de Victorias!
                        </p>
                    </td>
                </tr>
            </table>
        </div>
        HTML;

        return (new MailMessage)
            ->subject('CSAV Account Approved')
            ->view('mail.raw', ['html' => $html]);
    }

    protected function buildLoginUrl(): string
    {
        return url('/login');
    }

    public function toArray(object $notifiable): array
    {
        return [];
    }
}
