<?php

namespace App\Domain\External\ExportTo1c\Services;

use App\Domain\Orders\Enums\DocTypeEnum;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Layta\BuClient\Api\SellersApi;
use Layta\BuClient\Api\StoresApi;
use Layta\BuClient\ApiException;
use Layta\BuClient\Dto\PaginationTypeEnum as BuPaginationTypeEnum;
use Layta\BuClient\Dto\RequestBodyPagination as BuRequestPagination;
use Layta\BuClient\Dto\SearchSellersRequest;
use Layta\BuClient\Dto\SearchStoresRequest;
use Layta\BuClient\Dto\Seller;
use Layta\BuClient\Dto\Store;
use Layta\CustomersClient\Api\CustomersApi;
use Layta\CustomersClient\Dto\Customer;
use Layta\CustomersClient\Dto\CustomerTypeEnum;
use Layta\CustomersClient\Dto\PaginationTypeEnum as CustomerPaginationTypeEnum;
use Layta\CustomersClient\Dto\RequestBodyPagination;
use Layta\CustomersClient\Dto\RequestBodyPagination as CustomerRequestPagination;
use Layta\CustomersClient\Dto\SearchCustomersFilter;
use Layta\CustomersClient\Dto\SearchCustomersRequest;
use Layta\OffersClient\Api\OffersApi;
use Layta\OffersClient\Dto\Offer;
use Layta\OffersClient\Dto\PaginationTypeEnum as OffersPaginationTypeEnum;
use Layta\OffersClient\Dto\RequestBodyPagination as OffersRequestPagination;
use Layta\OffersClient\Dto\SearchOffersRequest;
use Layta\OmsClient\Api\OrdersApi;
use Layta\OmsClient\Dto\Order;
use Layta\PimClient\Api\ProductsApi;
use Layta\PimClient\Dto\PaginationTypeEnum as PimPaginationTypeEnum;
use Layta\PimClient\Dto\Product;
use Layta\PimClient\Dto\RequestBodyPagination as PimRequestPagination;
use Layta\PimClient\Dto\SearchProductsRequest;
use SimpleXMLElement;
use Spatie\ArrayToXml\ArrayToXml;

class DataCreatorService
{
    private Customer $customerInfo;
    private Order $orderInfo;

    private Seller $sellerInfo;

    /**
     * @param CustomersApi $customersApi
     * @param OrdersApi $ordersApi
     */
    public function __construct(
        private readonly CustomersApi $customersApi,
        private readonly OrdersApi    $ordersApi,
        private readonly StoresApi    $storesApi,
        private readonly ProductsApi  $productsApi,
        private readonly OffersApi    $offersApi,
        private readonly SellersApi   $sellersApi,
    ) {
    }

    public function getBody(): string
    {
        return "thats work";
    }

    public function getSendOrderShipmentTo1cBody(int $orderId): array
    {
        $this->getExternalDataForOrderShipments($orderId);

        return $this->getSendOrderShipmentBodies();
    }

    private function getSendOrderShipmentBodies(): array
    {
        $bodies = [];
        $deliveries = $this->orderInfo->getDeliveries();
        foreach ($deliveries as $delivery) {
            $shipments = $delivery->getShipments();
            foreach ($shipments as $shipment) {
                $this->shipment = $shipment;
                $this->store = $this->stores[$this->shipment->getStoreId()] ?? null;
                $this->sellerInfo = $this->sellers[$this->shipment->getSellerId()] ?? null;

                $this->prepareDataForDocuments();
                if (!$this->store->getIsMarketplaceStore()) {
                    $bodies[] = $this->getGlobalArrayForAcceptance();
                }
                $bodies[] = $this->getGlobalArrayForShipment();
            }
        }

        return $bodies;
    }

    private function getGlobalArrayForAcceptance(): string
    {
        $root = [
            'rootElementName' => 'soapenv:Envelope',
            '_attributes' => [
                'xmlns:soapenv' => 'http://www.w3.org/2003/05/soap-envelope/',
                'xmlns:ade' => 'http://ade.project',
            ],
        ];
        $array = [
            'soap:Header' => [],
            'soap:Body' => [
                'ade:Post' => [
                    'ade:request' => [
                        'ade:MsgID' => $this->getUniqueUuid(),
                        'ade:MsgType' => 'createAcceptanceOrderRequest_KA',
                        'ade:ExtDate' => $this->getExtData(),
                        'ade:Sender' => [
                            '_attributes' => [
                                'ID' => 'n4nISlWfVzY2pH/42u0NMw==',
                                'Name' => 'МП',
                            ],
                        ],
                        'ade:Receiver' => [
                            '_attributes' => [
                                'ID' => 'o7dB/wdKsrVCgNZ7gKKKCQ==',
                                'Name' => 'КА2',
                            ],
                        ],
                        'ade:MsgData' => $this->getMsgDataForAcceptance(),
                    ],
                ],
            ],
        ];
        $arrayToXml = new ArrayToXml($array, $root);

        return $arrayToXml->dropXmlDeclaration()->toXml();
    }

    private function getGlobalArrayForShipment(): string
    {
        $root = [
            'rootElementName' => 'soapenv:Envelope',
            '_attributes' => [
                'xmlns:soapenv' => 'http://www.w3.org/2003/05/soap-envelope/',
                'xmlns:ade' => 'http://ade.project',
            ],
        ];
        $array = [
            'soap:Header' => [],
            'soap:Body' => [
                'ade:Post' => [
                    'ade:request' => [
                        'ade:MsgID' => $this->getUniqueUuid(),
                        'ade:MsgType' => 'createShipmentOrderRequest_KA',
                        'ade:ExtDate' => $this->getExtData(),
                        'ade:Sender' => [
                            '_attributes' => [
                                'ID' => 'n4nISlWfVzY2pH/42u0NMw==',
                                'Name' => 'МП',
                            ],
                        ],
                        'ade:Receiver' => [
                            '_attributes' => [
                                'ID' => 'o7dB/wdKsrVCgNZ7gKKKCQ==',
                                'Name' => 'КА2',
                            ],
                        ],
                        'ade:MsgData' => $this->getMsgDataForShipment(),
                    ],
                ],
            ],
        ];
        $arrayToXml = new ArrayToXml($array, $root);

        return $arrayToXml->dropXmlDeclaration()->toXml();
    }

    private function getMsgDataForAcceptance(): string
    {
        $array = [
            'Orders' => [
                'Order' => [
                    '_attributes' => [
                        'OrderID' => $this->shipment->getUuid(),
                        'DocTypeID' => DocTypeEnum::ARRIVAL,
                        'DocNum' => $this->shipment->getNumber(),
                        'DocDate' => $this->shipment->getCreatedAt()->format('Y-m-d H:i:s'),
                        'DocSumm' => $this->shipment->getCost(),
                        'NDS_Include' => 'true',
                        'Comment' => $this->orderInfo->getClientComment(),
                    ],
                    'Stock' => [
                        '_attributes' => [
                            'StockID' => $this->store->getUuid(),
                            'StockName' => $this->store->getName(),
                        ],
                    ],
                    'Client_KA' => [
                        '_attributes' => [
                            'ClientID' => $this->sellerInfo->getUuid(),
                            'ClientName' => htmlspecialchars($this->sellerInfo->getLegalName()),
                            'INN' => $this->sellerInfo->getInn(),
                            'АctualАddress' => htmlspecialchars($this->sellerInfo->getFactAddress()),
                            'IsBuyer' => "false",
                            'ClientFullName' => htmlspecialchars($this->sellerInfo->getLegalNameFull()),
                        ],
                    ],
                ],
            ],
        ];

        $msgData = ArrayToXml::convert($array, [
            'rootElementName' => 'createAcceptanceOrderRequest_KA',
            '_attributes' => [
                'xmlns' => 'http://ws.toplogwms.ru/',
                'xmlns:xs' => 'http://www.w3.org/2001/XMLSchema',
                'xmlns:xsi' => 'http://www.w3.org/2001/XMLSchema-instance',
            ],
        ], true, 'UTF-8');

        $goods = $this->getGoodsData();
        $orderRows = $this->getOrderRowsData();

        $extractedXml = new SimpleXMLElement($msgData);
        foreach ($goods as $attributes) {
            $newItem = $extractedXml->Orders->addChild('Goods');
            foreach ($attributes as $key => $val) {
                $newItem->addAttribute($key, $val);
            }
        }
        foreach ($orderRows as $attributes) {
            $newItem = $extractedXml->Orders->addChild('OrderRows');
            foreach ($attributes as $key => $val) {
                $newItem->addAttribute($key, $val);
            }
        }

        return base64_encode($extractedXml->asXML());
    }

    private function getMsgDataForShipment(): string
    {
        $array = [
            'Orders' => [
                'Order' => [
                    '_attributes' => [
                        'OrderID' => $this->shipment->getUuid(),
                        'DocTypeID' => DocTypeEnum::SHIPMENT,
                        'DocNum' => $this->shipment->getNumber(),
                        'DocDate' => $this->shipment->getCreatedAt()->format('Y-m-d H:i:s'),
                        'DocSumm' => $this->shipment->getCost(),
                        'NDS_Include' => 'true',
                    ],
                    'Stock' => [
                        '_attributes' => [
                            'StockID' => $this->store->getUuid(),
                            'StockName' => $this->store->getName(),
                        ],
                    ],
                    'Client_KA' => [
                        '_attributes' => [
                            'ClientID' => $this->customerInfo->getUuid(),
                            'ClientName' => $this->getClientName(),
                            'Phone' => $this->customerInfo->getPhone(),
                            'INN' => $this->customerInfo->getPhone(),
                            'Email' => $this->customerInfo->getEmail(),
                            'АctualАddress' => $this->getАctualАddress(),
                            'IsBuyer' => "true",
                            'IsLegalPerson' => $this->getIsLegalPerson(),
                            'ClientFullName' => $this->getClientFullName(),
                        ],
                    ],
                ],
            ],
        ];

        $msgData = ArrayToXml::convert($array, [
            'rootElementName' => 'createShipmentOrderRequest_KA',
            '_attributes' => [
                'xmlns' => 'http://ws.toplogwms.ru/',
                'xmlns:xs' => 'http://www.w3.org/2001/XMLSchema',
                'xmlns:xsi' => 'http://www.w3.org/2001/XMLSchema-instance',
            ],
        ], true, 'UTF-8');

        $orderRows = $this->getOrderRowsData(false);

        $extractedXml = new SimpleXMLElement($msgData);
        foreach ($orderRows as $attributes) {
            $newItem = $extractedXml->Orders->addChild('OrderRows');
            foreach ($attributes as $key => $val) {
                $newItem->addAttribute($key, $val);
            }
        }

        return base64_encode($extractedXml->asXML());
    }

    private function getOrderRowsData(bool $showPlanQty = true): array
    {
        $attributes = [];
        foreach ($this->orderProducts as $item) {
            if ($showPlanQty) {
                $attributes[] = [
                    'GoodID' => $item['GoodID'],
                    'Quantity_plan' => $item['Quantity_plan'],
                    'Quantity' => $item['Quantity'],
                    'Price' => $item['Price'],
                    'BaseKeepingVariantType' => $item['BaseKeepingVariantType'],
                    'Coefficient' => $item['Coefficient'],
                    'Summ' => $item['Summ'],
                    'Stavka_NDS' => $item['Stavka_NDS'],
                    'Summ_NDS' => $item['Summ_NDS'],
                    'Summ_With_NDS' => $item['Summ_With_NDS'],
                ];
            } else {
                $attributes[] = [
                    'GoodID' => $item['GoodID'],
                    'Quantity' => $item['Quantity'],
                    'Price' => $item['Price'],
                    'BaseKeepingVariantType' => $item['BaseKeepingVariantType'],
                    'Coefficient' => $item['Coefficient'],
                    'Summ' => $item['Summ'],
                    'Stavka_NDS' => $item['Stavka_NDS'],
                    'Summ_NDS' => $item['Summ_NDS'],
                    'Summ_With_NDS' => $item['Summ_With_NDS'],
                ];
            }

        }

        return $attributes;
    }

    private function getGoodsData(): array
    {
        $attributes = [];
        foreach ($this->orderProducts as $item) {
            $attributes[] = [
                'GoodID' => $item['GoodID'],
                'Name' => $item['Name'],
                'Article' => $item['Article'],
                'BaseKeepingVariantType' => $item['BaseKeepingVariantType'],
                'FullName' => $item['FullName'],
                'IsService' => $item['IsService'],
            ];
        }

        return $attributes;
    }

    private function prepareDataForDocuments(): void
    {
        $items = $this->shipment->getOrderItems();
        $productTable = [];
        foreach ($items as $item) {
            $offer = $this->offers[$item->getOfferId()] ?? null;
            if (!$offer) {
                continue;
            }
            $product = $this->products[$offer->getProductId()] ?? null;
            if (!$product) {
                continue;
            }
            $productTable[] = [
                'GoodID' => $product->getUuid(),
                'Name' => $product->getName(),
                'Article' => $product->getVendorCode(),
                'BaseKeepingVariantType' => 796,
                'FullName' => $product->getName(),
                'IsService' => "false",
                'Quantity_plan' => $item->getQty(),
                'Quantity' => $item->getQty(),
                'Price' => $item->getPricePerOne(),
                'Coefficient' => 1,
                'Summ' => $item->getPrice(),
                'Stavka_NDS' => "НДС" . $product->getVat(),
                'Summ_NDS' => $item->getPriceVat(),
                'Summ_With_NDS' => $item->getPrice() - $item->getPriceVat(),
            ];
        }
        $this->orderProducts = $productTable;
    }

    private function getExternalDataForOrderShipments(int $orderId): void
    {
        $this->orderInfo = $this->ordersApi->getOrder($orderId, 'items,deliveries.shipments,deliveries.shipments.order_items')->getData();
        $filter = (new SearchCustomersFilter())
            ->setId($this->orderInfo->getCustomerId());
        $pagination = new CustomerRequestPagination(['limit' => 1, 'type' => CustomerPaginationTypeEnum::CURSOR]);
        $request = (new SearchCustomersRequest())
            ->setFilter($filter)
            ->setInclude('addresses')
            ->setPagination($pagination);
        $this->customerInfo = $this->customersApi->searchCustomers($request)->getData()[0];
        $this->getForeignEntitiesIds();
    }

    private function getForeignEntitiesIds(): void
    {
        $storeIds = [];
        $sellerIds = [];
        $offerIds = [];

        collect($this->orderInfo->getDeliveries())->each(function ($delivery) use (&$storeIds, &$sellerIds, &$offerIds) {
            collect($delivery->getShipments())->each(function ($shipment) use (&$storeIds, &$sellerIds, &$offerIds) {
                $storeIds[] = $shipment->getStoreId();
                $sellerIds[] = $shipment->getSellerId();
                collect($shipment->getOrderItems())->each(function ($item) use (&$storeIds, &$sellerIds, &$offerIds) {
                    $offerIds[] = $item->getOfferId();
                });
            });
        });

        $this->storeIds = array_unique($storeIds);
        $this->loadStores();
        $this->sellerIds = array_unique($sellerIds);
        $this->loadSellers();
        $this->offerIds = array_unique($offerIds);
        $this->loadOffers();
        $productIds = [];
        collect($this->offers)->each(function ($offer) use (&$productIds) {
            $productIds[] = $offer->getProductId();
        });
        $this->productIds = $productIds;
        $this->loadProducts();
    }

    /**
     * @throws \Layta\PimClient\ApiException
     */
    protected function loadProducts(): void
    {
        $pagination = new PimRequestPagination(['limit' => count($this->productIds), 'type' => PimPaginationTypeEnum::CURSOR]);
        $request = (new SearchProductsRequest())
            ->setFilter((object)['id' => $this->productIds])
            ->setPagination($pagination);

        $searchResult = $this->productsApi->searchProducts($request)->getData();
        if ($searchResult) {
            collect($searchResult)->each(fn (Product $item) => $this->products[$item->getId()] = $item);
        }
    }

    /**
     * @throws ApiException
     */
    protected function loadStores(): void
    {
        $pagination = new BuRequestPagination(['limit' => count($this->storeIds), 'type' => BuPaginationTypeEnum::CURSOR]);
        $request = (new SearchStoresRequest())
            ->setFilter((object)['id' => $this->storeIds])
            ->setPagination($pagination);

        $searchResult = $this->storesApi->searchStores($request)->getData();
        if ($searchResult) {
            collect($searchResult)->each(fn (Store $item) => $this->stores[$item->getId()] = $item);
        }
    }

    /**
     * @throws ApiException
     */
    protected function loadSellers(): void
    {
        $pagination = new BuRequestPagination(['limit' => count($this->sellerIds), 'type' => BuPaginationTypeEnum::CURSOR]);
        $request = (new SearchSellersRequest())
            ->setFilter((object)['id' => $this->sellerIds])
            ->setPagination($pagination);
        $searchResult = $this->sellersApi->searchSellers($request)->getData();

        if ($searchResult) {
            collect($searchResult)->each(fn (Seller $item) => $this->sellers[$item->getId()] = $item);
        }
    }

    /**
     * @throws \Layta\OffersClient\ApiException
     */
    protected function loadOffers(): void
    {
        $pagination = new OffersRequestPagination(['limit' => count($this->offerIds), 'type' => OffersPaginationTypeEnum::CURSOR]);
        $request = (new SearchOffersRequest())
            ->setFilter((object)['id' => $this->offerIds])
            ->setPagination($pagination);

        $searchResult = $this->offersApi->searchOffers($request)->getData();

        if ($searchResult) {
            collect($searchResult)->each(fn (Offer $item) => $this->offers[$item->getId()] = $item);
        }
    }

    private function getGlobalXmlForGetCustomer(): string
    {
        $root = [
            'rootElementName' => 'soapenv:Envelope',
            '_attributes' => [
                'xmlns:soapenv' => 'http://www.w3.org/2003/05/soap-envelope/',
                'xmlns:ade' => 'http://ade.project',
            ],
        ];
        $array = [
            'soap:Header' => [],
            'soap:Body' => [
                'ade:Post' => [
                    'ade:request' => [
                        'ade:MsgID' => $this->getUniqueUuid(),
                        'ade:MsgType' => 'createClientRequest_KA',
                        'ade:ExtDate' => $this->getExtData(),
                        'ade:Sender' => [
                            '_attributes' => [
                                'ID' => 'n4nISlWfVzY2pH/42u0NMw==',
                                'Name' => 'МП',
                            ],
                        ],
                        'ade:Receiver' => [
                            '_attributes' => [
                                'ID' => 'o7dB/wdKsrVCgNZ7gKKKCQ==',
                                'Name' => 'КА2',
                            ],
                        ],
                        'ade:MsgData' => $this->getCustomerTo1cMsgData(),
                    ],
                ],
            ],
        ];
        $arrayToXml = new ArrayToXml($array, $root);

        return $arrayToXml->dropXmlDeclaration()->toXml();
    }

    public function getCustomerTo1cBody(int $customerId): string
    {
        $this->getCustomerInfoFromExternalService($customerId);

        return $this->getGlobalXmlForGetCustomer();
    }

    private function getCustomerTo1cMsgData()
    {
        $array = [
            'Client_KA' => [
                '_attributes' => [
                    'ClientID' => $this->customerInfo->getUuid(),
                    'ClientName' => $this->getClientName(),
                    'INN' => $this->customerInfo->getInn(),
                    'Phone' => $this->customerInfo->getPhone(),
                    'Email' => $this->customerInfo->getEmail(),
                    'АctualАddress' => $this->getАctualАddress(),
                    'IsBuyer' => 'true',
                    'IsLegalPerson' => $this->getIsLegalPerson(),
                    'ClientFullName' => $this->getClientFullName(),
                ],
            ],
        ];

        $msgData = ArrayToXml::convert($array, [
            'rootElementName' => 'createClientRequest_KA',
            '_attributes' => [
                'xmlns' => 'http://ws.toplogwms.ru/',
                'xmlns:xs' => 'http://www.w3.org/2001/XMLSchema',
                'xmlns:xsi' => 'http://www.w3.org/2001/XMLSchema-instance',
            ],
        ], true, 'UTF-8');

        return base64_encode($msgData);
    }

    private function getClientName(): string
    {
        if (!$this->getClientType() == CustomerTypeEnum::LEGAL) {
            return htmlspecialchars($this->customerInfo->getFirstName());
        }

        return htmlspecialchars($this->customerInfo->getOrgName());
    }

    private function getАctualАddress(): string
    {
        if (!count($this->customerInfo->getAddresses())) {
            return '';
        }

        $filteredAddress = array_filter($this->customerInfo->getAddresses(), fn ($address) => $address["default"] == true);
        $address = reset($filteredAddress)->getAddress()->getAddressString();

        return htmlspecialchars($address);
    }

    private function getIsLegalPerson(): string
    {
        if (!$this->getClientType() == CustomerTypeEnum::LEGAL) {
            return "false";
        }

        return 'true';
    }

    private function getClientType(): CustomerTypeEnum
    {
        return $this->customerInfo->getType();
    }

    private function getClientFullName(): string
    {
        if (!$this->getClientType() == CustomerTypeEnum::LEGAL) {
            return htmlspecialchars($this->customerInfo->getFullName());
        }

        return htmlspecialchars($this->customerInfo->getOrgNameFull());
    }

    private function getParamByKeyAndVal(string $param, string $value): string
    {
        return $param . '="' . $value . '" ';
    }

    private function getUniqueUuid(): string
    {
        return Str::uuid();
    }

    private function getExtData(): string
    {
        return Carbon::now()->toDateTimeLocalString();
    }

    private function getCustomerInfoFromExternalService(int $customerId): void
    {
        $filter = (new SearchCustomersFilter())
            ->setId($customerId);
        $pagination = (new RequestBodyPagination())->setLimit(1);
        $request = (new SearchCustomersRequest())
            ->setFilter($filter)
            ->setInclude('addresses')
            ->setPagination($pagination);
        $this->customerInfo = $this->customersApi->searchCustomers($request)->getData()[0];
    }
}
