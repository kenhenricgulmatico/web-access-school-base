<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Stock types are now only:
 *   Supplies   ← Paper Supplies, Writing Materials, Art Supplies … (used up, never returned)
 *   Material   ← Furniture, materials, Equipment …               (borrowed and returned)
 *   Facilities ← Facility                                          (rooms / labs)
 *
 * resources.is_returnable is set from the type: Material = true, everything else = false.
 */
return new class extends Migration
{
    /** Old type name (lower-case) → new type. Anything not listed becomes Supplies. */
    private const MAP = [
        'paper supplies'    => 'Supplies',
        'writing materials' => 'Supplies',
        'art supplies'      => 'Supplies',
        'supplies'          => 'Supplies',
        'furniture'         => 'Material',
        'materials'         => 'Material',
        'material'          => 'Material',
        'equipment'         => 'Material',
        'facility'          => 'Facilities',
        'facilities'        => 'Facilities',
    ];

    public function up(): void
    {
        DB::transaction(function () {
            $ids = [];
            foreach (['Supplies', 'Material', 'Facilities'] as $name) {
                $ids[$name] = $this->ensureType($name);
            }

            foreach (DB::table('resource_types')->get() as $type) {
                $target = self::MAP[strtolower(trim($type->type_name))] ?? 'Supplies';

                if ((int) $type->id === (int) $ids[$target]) {
                    continue;
                }

                DB::table('resources')->where('resource_type_id', $type->id)
                    ->update(['resource_type_id' => $ids[$target]]);

                DB::table('resource_types')->where('id', $type->id)->delete();
            }

            DB::table('resources')->update([
                'is_returnable' => DB::raw('CASE WHEN resource_type_id = ' . (int) $ids['Material'] . ' THEN 1 ELSE 0 END'),
            ]);
        });
    }

    /** Reuse an existing type row (renamed) so its id stays stable; create it only if none fits. */
    private function ensureType(string $name): int
    {
        $existing = DB::table('resource_types')->where('type_name', $name)->value('id');
        if ($existing) {
            return (int) $existing;
        }

        // Rename the first old type that maps to this one (keeps the same id)
        foreach (DB::table('resource_types')->orderBy('id')->get() as $type) {
            if ((self::MAP[strtolower(trim($type->type_name))] ?? null) === $name) {
                DB::table('resource_types')->where('id', $type->id)
                    ->update(['type_name' => $name, 'updated_at' => now()]);
                return (int) $type->id;
            }
        }

        return (int) DB::table('resource_types')->insertGetId([
            'type_name'  => $name,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        // The old sub-types cannot be rebuilt; only the names are put back.
        DB::table('resource_types')->where('type_name', 'Facilities')->update(['type_name' => 'Facility']);
        DB::table('resource_types')->where('type_name', 'Material')->update(['type_name' => 'Furniture']);
        DB::table('resource_types')->where('type_name', 'Supplies')->update(['type_name' => 'Paper Supplies']);
    }
};
