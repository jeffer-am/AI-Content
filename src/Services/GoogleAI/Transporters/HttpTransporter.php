<?php

namespace AIMuse\Services\GoogleAI\Transporters;

use AIMuseVendor\GuzzleHttp\Client;
use AIMuse\Contracts\Transporter;
use AIMuseVendor\Psr\Http\Message\StreamInterface;

class HttpTransporter implements Transporter
{
  private string $baseUrl = 'https://generativelanguage.googleapis.com';
  private string $version = 'v1';
  private Client $client;
  public string $apiKey;

  public function __construct(string $apiKey)
  {
    $this->apiKey = $apiKey;
    $this->client = new Client([
      'base_uri' => $this->baseUrl,
    ]);
  }

  public function post(string $endpoint, array $request = [])
  {
    return $this->request('POST', $endpoint, [
      'json' => $request,
    ]);
  }

  public function stream(string $endpoint, array $request = []): StreamInterface
  {
    return $this->request('POST', $endpoint, [
      'json' => $request,
      'stream' => true,
    ]);
  }

  public function get(string $endpoint)
  {
    return $this->request('GET', $endpoint);
  }

  public function request(string $method, string $endpoint, array $options = [])
  {
    $options['query'] = [
      'key' => $this->apiKey,
    ];

    $response = $this->client->request($method, "{$this->version}/$endpoint", $options);

    if ($response->getStatusCode() !== 200) {
      throw new \Exception("Error Processing Request", 1);
    }

    if (isset($options['stream']) && $options['stream']) {
      return $response->getBody();
    }

    $body = $response->getBody()->getContents();
    $json = json_decode($body);

    return $json;
  }
}
