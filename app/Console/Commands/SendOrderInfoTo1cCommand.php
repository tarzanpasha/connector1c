<?php

namespace App\Console\Commands;

use App\Domain\Orders\Actions\SendOrderShipmentInfoTo1cAction;
use Illuminate\Console\Command;

class SendOrderInfoTo1cCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = '1c:order {orderId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Отправляет данные по заказу в 1с, принимая на вход id покупателя';

    /**
     * Execute the console command.
     *
     * @param SendOrderShipmentInfoTo1cAction $sendTo1cAction
     * @return int
     */
    public function handle(SendOrderShipmentInfoTo1cAction $sendTo1cAction): int
    {
        $orderId = $this->argument('orderId');
        if (!$orderId) {
            return Command::FAILURE;
        }
        $sendTo1cAction->execute($orderId);

        return Command::SUCCESS;
    }
}
