<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * ✅ The app used to run on UTC, so every time it saved (created_at, approved_at, returned_at…)
 *    is 8 hours behind Philippine time. config/app.php now uses Asia/Manila, so this moves
 *    the times already saved forward by 8 hours to match.
 *
 *    Not touched (they are already Philippine time):
 *      - dates and times people type in: request_date, start_time, end_time, maintenance_until …
 *      - due_back_at (a date only)
 *      - failed_jobs.failed_at (filled by MySQL itself, which is on Philippine time)
 *      - stock arrival date/time typed by the Admin (only rows the app stamped automatically move)
 *
 *    rollback() moves everything back by 8 hours.
 */
return new class extends Migration
{
    private const HOURS = 8;

    /** Framework tables — not shown anywhere in the app. */
    private const SKIP_TABLES = ['migrations', 'failed_jobs', 'password_reset_tokens', 'sessions', 'cache', 'cache_locks', 'jobs', 'job_batches'];

    public function up(): void
    {
        $this->shift(+self::HOURS);
    }

    public function down(): void
    {
        $this->shift(-self::HOURS);
    }

    private function shift(int $hours): void
    {
        // Tests use a fresh SQLite database created in Philippine time already — nothing to move.
        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }

        $op = $hours >= 0 ? '+' : '-';
        $h  = abs($hours);

        DB::transaction(function () use ($op, $h, $hours) {
            // 1) Every timestamp / datetime column the app writes
            $cols = DB::select(
                "SELECT table_name AS t, column_name AS c
                   FROM information_schema.columns
                  WHERE table_schema = DATABASE() AND data_type IN ('timestamp', 'datetime')
                  ORDER BY table_name, ordinal_position"
            );

            $byTable = [];
            foreach ($cols as $col) {
                if (! in_array($col->t, self::SKIP_TABLES, true)) {
                    $byTable[$col->t][] = $col->c;
                }
            }

            foreach ($byTable as $table => $columns) {
                // one UPDATE per table, every column at once
                $set = collect($columns)
                    ->map(fn ($c) => "`{$c}` = IF(`{$c}` IS NULL, NULL, `{$c}` {$op} INTERVAL {$h} HOUR)")
                    ->implode(', ');
                DB::statement("UPDATE `{$table}` SET {$set}");
            }

            // 2) Stock arrivals the app stamped automatically (time = the moment the row was saved)
            //    created_at has already moved, so compare against the shifted value.
            DB::statement(
                "UPDATE `stocks`
                    SET arrival_date = DATE(TIMESTAMP(arrival_date, arrival_time) {$op} INTERVAL {$h} HOUR),
                        arrival_time = TIME(TIMESTAMP(arrival_date, arrival_time) {$op} INTERVAL {$h} HOUR)
                  WHERE arrival_time IS NOT NULL
                    AND ABS(TIMESTAMPDIFF(MINUTE, TIMESTAMP(arrival_date, arrival_time), created_at {$this->undo($op)} INTERVAL {$h} HOUR)) <= 2"
            );

            // 3) Times saved inside audit-log details without a time zone ("2026-11-26 06:25:30").
            //    Values like "2026-11-26T06:25:30.000000Z" already say they are UTC and show correctly.
            DB::table('audit_logs')->whereNotNull('record')->orderBy('id')->select(['id', 'record'])
                ->chunkById(500, function ($logs) use ($hours) {
                    foreach ($logs as $log) {
                        $data = json_decode($log->record, true);
                        if (! is_array($data)) {
                            continue;
                        }
                        $changed = false;
                        $data = $this->shiftNaive($data, $hours, $changed);
                        if ($changed) {
                            DB::table('audit_logs')->where('id', $log->id)
                                ->update(['record' => json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
                        }
                    }
                });
        });
    }

    /** The opposite sign — used to find the original created_at. */
    private function undo(string $op): string
    {
        return $op === '+' ? '-' : '+';
    }

    private function shiftNaive(array $data, int $hours, bool &$changed): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->shiftNaive($value, $hours, $changed);
            } elseif (is_string($key) && is_string($value)
                && ($key === 'at' || str_ends_with($key, '_at'))
                && preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $value)) {
                // read it as plain clock time, add the hours, write it back the same way
                $data[$key] = gmdate('Y-m-d H:i:s', strtotime($value . ' UTC') + $hours * 3600);
                $changed = true;
            }
        }

        return $data;
    }
};
