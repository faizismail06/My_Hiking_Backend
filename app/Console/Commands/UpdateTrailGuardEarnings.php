<?php

namespace App\Console\Commands;

use App\Services\EarningsCalculationService;
use Illuminate\Console\Command;

class UpdateTrailGuardEarnings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'earnings:update {--user_id= : Update earnings for a specific user}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update trail guard earnings from paid orders';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $service = new EarningsCalculationService();

        if ($this->option('user_id')) {
            $userId = $this->option('user_id');
            $user = \App\Models\User::find($userId);

            if (!$user) {
                $this->error("User with ID {$userId} not found.");
                return 1;
            }

            $service->updateEarningsForUser($user);
            $this->info("Earnings updated for user: {$user->name}");
        } else {
            $service->updateAllEarnings();
            $this->info('Earnings updated for all trail guards.');
        }

        return 0;
    }
}
