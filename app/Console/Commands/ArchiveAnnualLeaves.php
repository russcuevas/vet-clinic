<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\LeaveAnnualLedger;
use Carbon\Carbon;

class ArchiveAnnualLeaves extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'leaves:annual-archive {--year= : The specific year to archive (defaults to previous year if run on Jan 1)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Snapshots and archives annual leave records (5 VL, 5 SL, 2 SPL) and SL monetization for permanent file keeping.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $year = $this->option('year') ? (int) $this->option('year') : Carbon::now()->subYear()->year;
        
        $this->info("Archiving annual leave records for year {$year} (File Keeping & Permanent Audit)...");

        $count = LeaveAnnualLedger::archiveYear($year, "Automated Annual Snapshot executed on " . now()->format('M d, Y h:i A'));

        $this->info("Successfully archived leave ledgers for {$count} active employees for Year {$year}!");

        return Command::SUCCESS;
    }
}
