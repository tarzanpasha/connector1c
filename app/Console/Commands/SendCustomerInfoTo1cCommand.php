<?php

namespace App\Console\Commands;

use App\Domain\Orders\Actions\SendCustomerInfoTo1cAction;
use Illuminate\Console\Command;

class SendCustomerInfoTo1cCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = '1c:customer {customerId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Отправляет данные по покупателю в 1с, принимая на вход id покупателя';

    public function handle(SendCustomerInfoTo1cAction $sendTo1cAction): int
    {
        $customerId = $this->argument('customerId');
        if (!$customerId) {
            return Command::FAILURE;
        }
        $result = $sendTo1cAction->execute($customerId);

        return Command::SUCCESS;
    }
}
