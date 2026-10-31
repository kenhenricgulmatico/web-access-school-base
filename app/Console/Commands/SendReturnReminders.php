<?php

namespace App\Console\Commands;

use App\Models\RequestItem;
use App\Notifications\ReturnReminderNotification;
use App\Support\ReturnTracking;
use Illuminate\Console\Command;

/**
 * Daily e-mail to borrowers whose items are due today, due tomorrow, or overdue.
 * Each borrower gets at most ONE e-mail per day, listing all their items.
 *
 * Runs automatically every morning (see routes/console.php) while the scheduler runs:
 *     php artisan schedule:work
 * Or run it by hand:
 *     php artisan returns:send-reminders
 */
class SendReturnReminders extends Command
{
    protected $signature = 'returns:send-reminders';

    protected $description = 'E-mail borrowers about items that are due back soon or overdue';

    public function handle(): int
    {
        $items = RequestItem::outstanding()
            ->with('request.user')
            ->whereDate('due_back_at', '<=', today()->addDay())
            ->where(fn ($q) => $q->whereNull('reminder_sent_at')->orWhereDate('reminder_sent_at', '<', today()))
            ->whereHas('request', fn ($q) => $q->where('status', 'approved'))
            ->get();

        $sent = 0;

        foreach ($items->groupBy(fn ($i) => $i->request->user_id) as $userItems) {
            $user = $userItems->first()->request->user;
            if (! $user?->email) {
                continue;
            }

            $user->notify(new ReturnReminderNotification(
                $userItems->map(fn ($i) => [
                    'name'     => $i->item_name,
                    'quantity' => (int) $i->quantity,
                    'due'      => $i->due_back_at->format('M d, Y'),
                    'label'    => ReturnTracking::label($i),
                    'overdue'  => $i->isOverdue(),
                ])->values()->all()
            ));

            RequestItem::whereIn('id', $userItems->pluck('id'))->update(['reminder_sent_at' => now()]);
            $sent++;
        }

        $this->info("Sent {$sent} reminder e-mail(s) for {$items->count()} item(s).");

        return self::SUCCESS;
    }
}
