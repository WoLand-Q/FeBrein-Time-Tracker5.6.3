<?php

namespace App\Console\Commands;

use App\Services\Admin\UserTimeLogService;
use Illuminate\Console\Command;

class DailyEventCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'event:daily';

    /**
     * The console command description.
     *
     * @var string
     */

    protected $description = 'Виконує подію щодня о 23:59';
    /**
     * Execute the console command.
     */
    public function handle()
    {
        /** @var UserTimeLogService $service */
        $service = resolve(UserTimeLogService::class);
        $service->autoSaveDay();
        $this->info('Подія виконана успішно');
    }
}
