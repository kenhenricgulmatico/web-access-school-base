<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Resource;
use App\Models\ResourceType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class MonthlyRequestsTestSeeder extends Seeder
{
    /**
     * How many years back to generate data for.
     */
    private int $yearsBack = 2;

    /**
     * Ratio of requests that go to Program Heads vs. other users.
     * 0.85 = 85% of seeded requests come from program heads.
     */
    private float $programHeadShare = 0.85;

    /**
     * Ratio of requests that get a matching request_approvals row.
     */
    private float $approvalRowShare = 0.9;

    public function run(): void
    {
        // ─────────────────────────────────────────────────────────────
        // 0. Ensure we have at least one department
        // ─────────────────────────────────────────────────────────────
        if (Department::count() === 0) {
            $this->command->info('No departments found — creating default departments.');
            foreach (['Computer Studies', 'Engineering', 'Business', 'Education'] as $name) {
                Department::create(['department_name' => $name]);
            }
        }

        // ─────────────────────────────────────────────────────────────
        // 0b. Ensure we have at least one resource type + resource
        // ─────────────────────────────────────────────────────────────
        $resourceType = ResourceType::firstOrCreate(['type_name' => ResourceType::SUPPLIES]);

        $paper = Resource::firstOrCreate(
            ['resource_name' => 'Bond Paper A4', 'resource_type_id' => $resourceType->id],
            [
                'description'        => 'Bond Paper A4',
                'quantity_available' => 500,
                'unit'               => 'Ream',
                'status'             => 'available',
            ]
        );

        $markerType = ResourceType::firstOrCreate(['type_name' => ResourceType::SUPPLIES]);
        $marker = Resource::firstOrCreate(
            ['resource_name' => 'Marker', 'resource_type_id' => $markerType->id],
            [
                'description'        => 'Marker',
                'quantity_available' => 200,
                'unit'               => 'Set',
                'status'             => 'available',
            ]
        );

        $allResources = Resource::where('status', 'available')->get();
        if ($allResources->isEmpty()) {
            $this->command->warn('No resources available after seeding defaults. Aborting.');
            return;
        }

        // ─────────────────────────────────────────────────────────────
        // 1. Ensure we have users in each department — create program
        //    heads + faculty on the fly if missing.
        // ─────────────────────────────────────────────────────────────
        $departments = Department::all();

        $programHeadsByDept = [];
        $othersByDept       = [];

        foreach ($departments as $dept) {
            $ph = User::role('program head')
                ->where('department_id', $dept->id)
                ->first();

            if (! $ph) {
                $ph = User::create([
                    'name'          => 'PH ' . $dept->department_name,
                    'email'         => 'ph.' . strtolower(str_replace(' ', '.', $dept->department_name)) . '@csav.edu.ph',
                    'password'      => Hash::make('password'),
                    'status'        => 'approved',
                    'department_id' => $dept->id,
                ]);
                $ph->assignRole('program head');

                $this->command->info("Created program head for {$dept->department_name}: {$ph->email}");
            }

            $programHeadsByDept[$dept->id] = [$ph->id];

            // Faculty
            $faculty = User::role('faculty')
                ->where('department_id', $dept->id)
                ->first();

            if (! $faculty) {
                $faculty = User::create([
                    'name'          => 'Faculty ' . $dept->department_name,
                    'email'         => 'faculty.' . strtolower(str_replace(' ', '.', $dept->department_name)) . '@csav.edu.ph',
                    'password'      => Hash::make('password'),
                    'status'        => 'approved',
                    'department_id' => $dept->id,
                ]);
                $faculty->assignRole('faculty');
            }

            // ✅ No student role anymore — faculty are the requesters
            $othersByDept[$dept->id] = [$faculty->id];
        }

        // ─────────────────────────────────────────────────────────────
        // 2. Give each department some allocated stock (so PH pages
        //    have something to show in "My Department's Materials")
        // ─────────────────────────────────────────────────────────────
        foreach ($departments as $dept) {
            foreach ($allResources as $resource) {
                DB::table('resource_all_locations')->updateOrInsert(
                    [
                        'resource_id'   => $resource->id,
                        'department_id' => $dept->id,
                    ],
                    [
                        'allocated_quantity' => 100,
                        'created_at'         => now(),
                        'updated_at'         => now(),
                    ]
                );
            }
        }

        // ─────────────────────────────────────────────────────────────
        // 3. Generate the requests
        // ─────────────────────────────────────────────────────────────
        $monthlyShape = [
            1 => 2, 2 => 3, 3 => 5, 4 => 6, 5 => 4, 6 => 1,
            7 => 1, 8 => 4, 9 => 8, 10 => 6, 11 => 3, 12 => 2,
        ];

        $requestRows = [];
        $currentYear  = (int) now()->year;
        $earliestYear = $currentYear - $this->yearsBack;

        foreach (range($earliestYear, $currentYear) as $year) {
            $yearFactor = match ($currentYear - $year) {
                0 => 1.00,
                1 => 0.75,
                2 => 0.55,
                default => 0.40,
            };

            foreach ($departments as $dept) {
                $deptMultiplier = fake()->randomFloat(2, 0.5, 1.5);
                $multiplier     = $deptMultiplier * $yearFactor;

                foreach ($monthlyShape as $month => $base) {
                    if ($year === $currentYear && $month > (int) now()->month) {
                        continue;
                    }

                    $count = max(0, (int) round($base * $multiplier) + fake()->numberBetween(-1, 1));

                    for ($i = 0; $i < $count; $i++) {
                        $useProgramHead = fake()->randomFloat(2, 0, 1) < $this->programHeadShare;

                        $pool = $useProgramHead
                            ? ($programHeadsByDept[$dept->id] ?? [])
                            : ($othersByDept[$dept->id] ?? []);

                        if (empty($pool)) {
                            $pool = $programHeadsByDept[$dept->id] ?? [];
                        }

                        if (empty($pool)) {
                            continue;
                        }

                        $userId = fake()->randomElement($pool);

                        $date = fake()->dateTimeBetween(
                            "{$year}-{$month}-01",
                            date('Y-m-t', strtotime("{$year}-{$month}-01"))
                        );

                        $requestRows[] = [
                            'user_id'                          => $userId,
                            'department_id'                    => $dept->id,
                            'request_type_id'                  => fake()->randomElement([1, 2]),
                            'purpose'                          => fake()->sentence(4),
                            'status'                           => fake()->randomElement(['pending', 'approved', 'rejected']),
                            'current_responsibility_center_id' => null,
                            'created_at'                       => $date,
                            'updated_at'                       => $date,
                        ];
                    }
                }
            }
        }

        foreach (array_chunk($requestRows, 200) as $chunk) {
            DB::table('requests')->insert($chunk);
        }

        // ─────────────────────────────────────────────────────────────
        // 4. Approval rows for approved/rejected requests
        // ─────────────────────────────────────────────────────────────
        $requestsForApproval = DB::table('requests')
            ->whereIn('status', ['approved', 'rejected'])
            ->select('id', 'department_id', 'status', 'updated_at')
            ->get();

        $approvalRows = [];

        foreach ($requestsForApproval as $req) {
            if (fake()->randomFloat(2, 0, 1) > $this->approvalRowShare) {
                continue;
            }

            $phId = $programHeadsByDept[$req->department_id][0] ?? null;
            if (! $phId) continue;

            $approvalRows[] = [
                'request_id'  => $req->id,
                'approver_id' => $phId,
                'status'      => $req->status,
                'remarks'     => null,
                'approved_at' => $req->updated_at,
                'created_at'  => $req->updated_at,
                'updated_at'  => $req->updated_at,
            ];
        }

        foreach (array_chunk($approvalRows, 200) as $chunk) {
            DB::table('request_approvals')->insert($chunk);
        }

        // ─────────────────────────────────────────────────────────────
        // 5. Items per request
        // ─────────────────────────────────────────────────────────────
        $allRequestIds = DB::table('requests')->pluck('id')->all();
        $allResourceIds = $allResources->pluck('id')->all();
        $resourceMap = $allResources->keyBy('id');

        $itemRows = [];

        foreach ($allRequestIds as $reqId) {
            $numItems = fake()->numberBetween(1, 3);
            $usedIds = [];

            for ($j = 0; $j < $numItems; $j++) {
                $rid = fake()->randomElement($allResourceIds);
                if (in_array($rid, $usedIds)) continue;
                $usedIds[] = $rid;

                $resource = $resourceMap->get($rid);
                if (! $resource) continue;

                $itemRows[] = [
                    'request_id'   => $reqId,
                    'resource_id'  => $rid,
                    'item_name'    => $resource->resource_name,
                    'quantity'     => fake()->numberBetween(1, 15),
                    'request_date' => now()->subDays(fake()->numberBetween(1, 90))->toDateString(),
                    'start_time'   => null,
                    'end_time'     => null,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ];
            }
        }

        foreach (array_chunk($itemRows, 200) as $chunk) {
            DB::table('request_items')->insert($chunk);
        }

        // ─────────────────────────────────────────────────────────────
        // 6. Report
        // ─────────────────────────────────────────────────────────────
        $phIds = collect($programHeadsByDept)->flatten()->all();
        $phCount = collect($requestRows)->whereIn('user_id', $phIds)->count();
        $otherCount = count($requestRows) - $phCount;

        $this->command->info(
            'Seeded ' . count($requestRows) . ' requests, ' .
            count($approvalRows) . ' approval rows, ' .
            count($itemRows) . ' items across ' .
            ($this->yearsBack + 1) . ' years (' . $earliestYear . '–' . $currentYear . ').'
        );
        $this->command->info(
            "  Program Head requests: {$phCount}  |  Other (Student/Faculty) requests: {$otherCount}"
        );
    }
}
