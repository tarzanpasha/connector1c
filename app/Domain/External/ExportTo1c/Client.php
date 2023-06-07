<?php

namespace App\Domain\External\ExportTo1c;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Request;

final class Client
{
    /** @var GuzzleClient */
    private GuzzleClient $http;

    public function __construct(Configuration $configuration)
    {
        $this->http = new GuzzleClient([
            'base_uri' => $configuration->getHost(),
            'http_errors' => false,
        ]);
        $this->url = $configuration->getHost();
        $this->headers = [
            'Content-Type' => 'text/xml',
            'Authorization' => 'Basic ' . $configuration->getAuth(),
        ];

    }

    public function apiRequest(string $body): mixed
    {

        $request = new Request('POST', $this->url, $this->headers, $body);

        $response = $this->http->send($request);

        $json = $response->getBody()->getContents();

        return json_decode($json, true);
    }
}
