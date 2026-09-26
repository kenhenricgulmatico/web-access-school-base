<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MonthlyRequestsTestSeeder extends Seeder
{
    public function run(): void
    {
        $departments = Department::pluck('id', 'department_name');

        if ($departments->isEmpty()) {
            $this->command->warn('No departments found. Seed departments first.');
            return;
        }

        // Grab (or fall back to) a user per department to attach requests to
        $usersByDept = [];
        foreach ($departments as $name => $id) {
            $user = User::where('department_id', $id)->first();
            if (! $user) {
                $user = User::first(); // fallback so seeder never breaks
            }
            $usersByDept[$id] = $user?->id;
        }

        if (collect($usersByDept)->filter()->isEmpty()) {
            $this->command->warn('No users found. Seed users first.');
            return;
        }

        $year = now()->year;

        // Roughly bell-shaped counts per month, mimicking real school activity
        $monthlyShape = [
            1 => 2, 2 => 3, 3 => 5, 4 => 6, 5 => 4, 6 => 1,
            7 => 1, 8 => 4, 9 => 8, 10 => 6, 11 => 3, 12 => 2,
        ];

        $rows = [];

        foreach ($departments as $name => $deptId) {
            $userId = $usersByDept[$deptId] ?? null;
            if (! $userId) continue;

            // Vary the intensity per department so lines don't overlap perfectly
            $multiplier = fake()->randomFloat(2, 0.5, 1.5);

            foreach ($monthlyShape as $month => $base) {
                $count = max(0, (int) round($base * $multiplier) + fake()->numberBetween(-1, 1));

                for ($i = 0; $i < $count; $i++) {
                    $date = fake()->dateTimeBetween(
                        "{$year}-{$month}-01",
                        date('Y-m-t', strtotime("{$year}-{$month}-01"))
                    );

                    $rows[] = [
                        'user_id' => $userId,
                        'department_id' => $deptId,
                        'request_type_id' => fake()->randomElement([1, 2]),
                        'purpose' => fake()->sentence(4),
                        'status' => fake()->randomElement(['pending', 'approved', 'rejected']),
                        'current_responsibility_center_id' => null,
                        'created_at' => $date,
                        'updated_at' => $date,
                    ];
                }
            }
        }

        foreach (array_chunk($rows, 200) as $chunk) {
            DB::table('requests')->insert($chunk);
        }

        $this->command->info('Seeded ' . count($rows) . ' test requests across all 12 months.');
    }
}
