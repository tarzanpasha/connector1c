<?php

namespace App\Domain\External\ExportTo1c\Api;

use App\Domain\External\ExportTo1c\Client;
use App\Domain\External\ExportTo1c\Services\DataCreatorService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class ExportTo1cApi
{
    public function __construct(readonly Client $client, readonly DataCreatorService $service)
    {
    }

    public function zaglushka(): string
    {
        return $this->service->getBody();
    }

    public function sendOrderShipmentTo1c(int $orderId): array
    {
        $bodies = $this->getSendOrderShipmentTo1cBody($orderId);
        $response = [];
        foreach ($bodies as $body) {
            try {
                $response[] = $this->client->apiRequest($body);
                sleep(1);
            } catch (GuzzleException $e) {
                Log::error($e->getMessage());
            }
        }

        return $response;
    }

    public function sendCustomerInfoTo1c(int $customerId): void
    {
        try {
            $response = $this->client->apiRequest($this->getCustomerInfoTo1cBody($customerId));
        } catch (GuzzleException $e) {
            Log::error($e->getMessage());
        }
    }

    private function getSendOrderShipmentTo1cBody(int $orderId): array
    {
        return $this->service->getSendOrderShipmentTo1cBody($orderId);

    }

    private function getCustomerInfoTo1cBody(int $customerId): string
    {
        return $this->service->getCustomerTo1cBody($customerId);
    }
}
