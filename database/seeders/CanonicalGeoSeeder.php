<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\GeoState;
use App\Models\GeoDistrict;
use App\Models\GeoAssemblySegment;
use App\Models\GeoMandal;
use App\Models\GeoPanchayat;

class CanonicalGeoSeeder extends Seeder
{
    /**
     * Seed reviewed canonical geographic master records required by initial production data.
     * Note: Cuddapah is NOT seeded as an alias here; it remains unresolved until Admin explicitly approves it.
     */
    public function run(): void
    {
        // 1. Canonical State
        $state = GeoState::firstOrCreate(
            ['country' => 'India', 'name' => 'Andhra Pradesh'],
            ['code' => 'AP', 'is_active' => true]
        );

        // 2. Canonical District (YSR Kadapa)
        $district = GeoDistrict::firstOrCreate(
            ['state_id' => $state->id, 'name' => 'YSR Kadapa'],
            ['code' => 'KDP', 'is_active' => true]
        );

        // 3. Canonical Assembly Segment (Badvel)
        $assembly = GeoAssemblySegment::firstOrCreate(
            ['district_id' => $district->id, 'name' => 'Badvel'],
            ['code' => 'BDV', 'is_active' => true]
        );

        // 4. Canonical Mandal (Porumamilla)
        $mandal = GeoMandal::firstOrCreate(
            ['district_id' => $district->id, 'name' => 'Porumamilla'],
            ['assembly_segment_id' => $assembly->id, 'code' => 'PRM', 'is_active' => true]
        );

        // 5. Canonical Panchayat (Akkalareddypalli)
        GeoPanchayat::firstOrCreate(
            ['mandal_id' => $mandal->id, 'name' => 'Akkalareddypalli'],
            ['pincode' => '516193', 'is_active' => true]
        );
    }
}
