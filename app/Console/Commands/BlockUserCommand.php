<?php

namespace App\Console\Commands;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class BlockUserCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'block:user';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Block user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $inactiveUsers = User::where('last_login_at', '<', Carbon::now()->subDays(3))->get();

        foreach ($inactiveUsers as $user) {
            $user->update(['status' => 'block']);
        }

        $this->info(count($inactiveUsers) . ' inactive users have been blocked.');
        return 0;
    }
}
