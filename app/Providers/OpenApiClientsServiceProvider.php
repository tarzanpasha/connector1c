<?php

namespace App\Providers;

use Ackintosh\Ganesha;
use Ackintosh\Ganesha\Builder;
use Ackintosh\Ganesha\GuzzleMiddleware;
use Ackintosh\Ganesha\Storage\Adapter\Apcu as ApcuAdapter;
use Ensi\GuzzleMultibyte\BodySummarizer;
use Ensi\LaravelInitialEventPropagation\PropagateInitialEventLaravelGuzzleMiddleware;
use Ensi\LaravelMetrics\Guzzle\GuzzleMiddleware as MetricsMiddleware;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Middleware;
use GuzzleHttp\Utils;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Layta\BasketsClient\BasketsClientProvider;
use Layta\BuClient\BuClientProvider;
use Layta\CatalogCacheClient\CatalogCacheClientProvider;
use Layta\CmsClient\CmsClientProvider;
use Layta\CustomerAuthClient\CustomerAuthClientProvider;
use Layta\CustomersClient\CustomersClientProvider;
use Layta\LogisticClient\LogisticClientProvider;
use Layta\MarketingClient\MarketingClientProvider;
use Layta\OffersClient\OffersClientProvider;
use Layta\OmsClient\OmsClientProvider;
use Layta\PimClient\PimClientProvider;
use LogicException;

class OpenApiClientsServiceProvider extends ServiceProvider
{
    private const DEFAULT_TIMEOUT = 30;

    public function register(): void
    {
        $handler = $this->configureHandler();

        $this->registerService(
            handler: $handler,
            domain: 'catalog',
            serviceName: 'catalog-cache',
            configurationClassName: CatalogCacheClientProvider::$configuration,
            apisClassNames: CatalogCacheClientProvider::$apis
        );

        $this->registerService(
            handler: $handler,
            domain: 'catalog',
            serviceName: 'offers',
            configurationClassName: OffersClientProvider::$configuration,
            apisClassNames: OffersClientProvider::$apis
        );

        $this->registerService(
            handler: $handler,
            domain: 'catalog',
            serviceName: 'pim',
            configurationClassName: PimClientProvider::$configuration,
            apisClassNames: PimClientProvider::$apis
        );

        $this->registerService(
            handler: $handler,
            domain: 'customers',
            serviceName: 'customer-auth',
            configurationClassName: CustomerAuthClientProvider::$configuration,
            apisClassNames: CustomerAuthClientProvider::$apis
        );

        $this->registerService(
            handler: $handler,
            domain: 'customers',
            serviceName: 'customers',
            configurationClassName: CustomersClientProvider::$configuration,
            apisClassNames: CustomersClientProvider::$apis
        );

        $this->registerService(
            handler: $handler,
            domain: 'orders',
            serviceName: 'baskets',
            configurationClassName: BasketsClientProvider::$configuration,
            apisClassNames: BasketsClientProvider::$apis
        );

        $this->registerService(
            handler: $handler,
            domain: 'orders',
            serviceName: 'oms',
            configurationClassName: OmsClientProvider::$configuration,
            apisClassNames: OmsClientProvider::$apis
        );

        $this->registerService(
            handler: $handler,
            domain: 'logistic',
            serviceName: 'logistic',
            configurationClassName: LogisticClientProvider::$configuration,
            apisClassNames: LogisticClientProvider::$apis
        );

        $this->registerService(
            handler: $handler,
            domain: 'units',
            serviceName: 'bu',
            configurationClassName: BuClientProvider::$configuration,
            apisClassNames: BuClientProvider::$apis
        );

        $this->registerService(
            handler: $handler,
            domain: 'marketing',
            serviceName: 'marketing',
            configurationClassName: MarketingClientProvider::$configuration,
            apisClassNames: MarketingClientProvider::$apis
        );

        $this->registerService(
            handler: $handler,
            domain: 'cms',
            serviceName: 'cms',
            configurationClassName: CmsClientProvider::$configuration,
            apisClassNames: CmsClientProvider::$apis
        );
    }

    private function configureHandler(): HandlerStack
    {
        $stack = new HandlerStack(Utils::chooseHandler());

        $stack->push(Middleware::httpErrors(new BodySummarizer()), 'http_errors');
        $stack->push(Middleware::redirect(), 'allow_redirects');
        $stack->push(Middleware::prepareBody(), 'prepare_body');
        if (!config('ganesha.disable_middleware', false)) {
            $stack->push($this->configureGaneshaMiddleware());
        }

        $stack->push(new PropagateInitialEventLaravelGuzzleMiddleware());
        $stack->push(MetricsMiddleware::middleware());

        if (config('app.http_debug')) {
            $stack->push($this->configureLoggerMiddleware(), 'logger');
        }

        return $stack;
    }

    private function configureLoggerMiddleware(): callable
    {
        $logger = logger()->channel('http_client');
        $format = "{req_headers}\n{req_body}\n\n{res_headers}\n{res_body}\n\n";
        $formatter = new MessageFormatter($format);

        return Middleware::log($logger, $formatter, 'debug');
    }

    private function configureGaneshaMiddleware(): GuzzleMiddleware
    {
        $config = config('ganesha');

        $ganesha = Builder::withRateStrategy()
            ->timeWindow($config['time_window'])
            ->failureRateThreshold($config['failure_rate_threshold'])
            ->minimumRequests($config['minimum_requests'])
            ->intervalToHalfOpen($config['interval_to_half_open'])
            ->adapter(new ApcuAdapter())
            ->build();


        $ganesha->subscribe(function ($event, $service, $message) {
            switch ($event) {
                case Ganesha::EVENT_TRIPPED:
                    Log::warning(
                        "Ganesha has tripped! It seems that a failure has occurred in {$service}. {$message}."
                    );

                    break;
                case Ganesha::EVENT_CALMED_DOWN:
                    Log::info(
                        "The failure in {$service} seems to have calmed down :). {$message}."
                    );

                    break;
                case Ganesha::EVENT_STORAGE_ERROR:
                    Log::error($message);

                    break;
            }
        });

        return new GuzzleMiddleware($ganesha);
    }

    private function registerService(HandlerStack $handler, string $domain, string $serviceName, string $configurationClassName, array $apisClassNames): void
    {
        $config = config("openapi-clients.$domain.$serviceName");
        if (!$config) {
            throw new LogicException("Config openapi-clients.$domain.$serviceName is not found");
        }

        $baseUri = $config['base_uri'];
        $this->app->bind($this->trimFQCN($configurationClassName), fn () => (new $configurationClassName())->setHost($baseUri));
        foreach ($apisClassNames as $api) {
            $this->app->when($this->trimFQCN($api))
                ->needs(ClientInterface::class)
                ->give(fn () => new Client([
                    'handler' => $handler,
                    'base_uri' => $baseUri,
                    'ganesha.service_name' => $domain . '_' . $serviceName,
                    'timeout' => $config['timeout'] ?? self::DEFAULT_TIMEOUT,
                ]));
        }
    }

    private function trimFQCN(string $name): string
    {
        return ltrim($name, '\\');
    }
}
