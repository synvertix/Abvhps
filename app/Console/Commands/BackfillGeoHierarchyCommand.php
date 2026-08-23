<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\GeoHierarchyBackfillService;

class BackfillGeoHierarchyCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'volunteer-cadre:backfill-geography
                            {--dry-run : Perform analysis only with zero database writes (default)}
                            {--force : Persist deterministic non-conflicting mappings to database}
                            {--only-volunteers : Process only volunteer records}
                            {--only-memberships : Process only membership records}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Safely analyze and backfill canonical 5-tier geography and 6-level President cadres';

    /**
     * Execute the console command.
     */
    public function handle(GeoHierarchyBackfillService $backfillService): int
    {
        $hasDryRun = (bool)$this->option('dry-run');
        $hasForce = (bool)$this->option('force');
        $onlyVolunteers = (bool)$this->option('only-volunteers');
        $onlyMemberships = (bool)$this->option('only-memberships');

        // 1. Guard against mutually exclusive mode options
        if ($hasDryRun && $hasForce) {
            $this->error('Choose either --dry-run or --force, not both.');
            return self::FAILURE;
        }

        // 2. Guard against mutually exclusive scope options
        if ($onlyVolunteers && $onlyMemberships) {
            $this->error('Choose either --only-volunteers or --only-memberships, not both.');
            return self::FAILURE;
        }

        // 3. Default to read-only dry-run unless --force is explicitly provided
        $isDryRun = !$hasForce;

        $this->newLine();
        if ($isDryRun) {
            $this->info('===============================================================');
            $this->info('  READ-ONLY DRY-RUN - ZERO DATABASE WRITES WILL BE PERFORMED   ');
            $this->info('===============================================================');
        } else {
            $this->warn('===============================================================');
            $this->warn('  LIVE PERSISTENCE MODE - WRITING DETERMINISTIC MATCHES        ');
            $this->warn('===============================================================');
        }
        $this->newLine();

        $metrics = $backfillService->run($isDryRun, $onlyVolunteers, $onlyMemberships);

        // Render Summary Table
        $this->table(
            ['Metric Description', 'Count'],
            [
                ['Volunteers Scanned', $metrics['volunteers_scanned']],
                ['Memberships Scanned', $metrics['memberships_scanned']],
                ['Already Mapped / Verified', $metrics['already_mapped']],
                ['Deterministic Full Matches (5 Tiers)', $metrics['would_match_full']],
                ['Deterministic Partial Matches (Upper Tiers)', $metrics['would_match_partial']],
                ['Cadre Conflicts Flagged (Needs Review)', $metrics['cadre_conflicts']],
                ['Geographic Conflicts Flagged (Needs Review)', $metrics['geographic_conflicts']],
                ['Remaining Unmapped (Needs Master Data)', $metrics['would_remain_unmapped']],
                ['Matched State Tiers', $metrics['matched_states']],
                ['Matched District Tiers', $metrics['matched_districts']],
                ['Matched Assembly Segment Tiers', $metrics['matched_assembly_segments']],
                ['Matched Mandal Tiers', $metrics['matched_mandals']],
                ['Matched Panchayat Tiers', $metrics['matched_panchayats']],
                ['Database Records Persisted', $metrics['persisted_updates']],
            ]
        );

        $this->newLine();
        if ($isDryRun) {
            $this->comment('Dry-run complete. 0 database rows were modified.');
            $this->comment('To persist deterministic non-conflicting matches, execute with: --force');
        } else {
            $this->info("Persistence complete. {$metrics['persisted_updates']} records successfully updated.");
        }
        $this->newLine();

        return self::SUCCESS;
    }
}
