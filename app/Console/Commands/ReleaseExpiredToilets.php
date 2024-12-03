<?php
// app/Console/Commands/ReleaseExpiredToilets.php
namespace App\Console\Commands;

use App\Models\Toilet;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ReleaseExpiredToilets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'toilets:release-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Release all toilets where occupation has expired';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        Log::notice("Releasing");
        $expiredCount = Toilet::where('is_occupied', true)
            ->where('occupation_expires_at', '<', now())
            ->get()
            ->each(function($toilet) {
                $toilet->release();
                $this->info("Released toilet #{$toilet->number} in {$toilet->washroom->name}");
            })
            ->count();

        $this->info("Released {$expiredCount} expired toilet occupations");
    }
}
