<?php

namespace App\Console\Commands;

use App\Domain\External\ExportTo1c\Api\ExportTo1cApi;
use App\Domain\External\ExportTo1c\Client;
use App\Domain\External\ExportTo1c\Configuration;
use App\Domain\External\ExportTo1c\Services\DataCreatorService;
use Illuminate\Console\Command;

class SendTo1cTestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = '1c:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Команда для отладки функционала отправки в 1с';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(ExportTo1cApi $exportTo1cApi)
    {
        $exportTo1cApi = new ExportTo1cApi(new Client(new Configuration()), new DataCreatorService());
        info($exportTo1cApi->zaglushka());

        return Command::SUCCESS;
    }
}
