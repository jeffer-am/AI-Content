<?php

namespace AIMuse\Services\OpenAI\Transporters;

use Exception;
use AIMuseVendor\GuzzleHttp\Client;
use AIMuse\Contracts\Transporter;
use AIMuse\Exceptions\ControllerException;
use AIMuse\Services\OpenAI\Exceptions\ContextLengthExceededException;
use AIMuse\Services\OpenAI\Exceptions\InvalidRequestException;
use AIMuse\Services\OpenAI\Exceptions\RateLimitExceededException;
use AIMuseVendor\Psr\Http\Message\StreamInterface;
use AIMuseVendor\GuzzleHttp\Exception\RequestException;
use AIMuseVendor\Illuminate\Support\Facades\Log;

class HttpTransporter implements Transporter
{
  private string $baseUrl = 'https://api.openai.com';
  private string $version = 'v1';
  private Client $client;
  public string $apiKey;

  public function __construct(string $apiKey)
  {
    $this->apiKey = $apiKey;
    $this->client = new Client([
      'base_uri' => $this->baseUrl,
      'headers' => [
        'Authorization' => 'Bearer ' . $this->apiKey
      ]
    ]);
  }

  public function post(string $endpoint, array $request = [])
  {
    return $this->request('POST', $endpoint, [
      'json' => $request,
    ]);
  }

  public function get(string $endpoint)
  {
    return $this->request('GET', $endpoint);
  }

  public function stream(string $endpoint, array $request = []): StreamInterface
  {
    return $this->request('POST', $endpoint, [
      'json' => $request,
      'stream' => true,
    ]);
  }

  public function request(string $method, string $endpoint, array $options = [])
  {
    try {
      $response = $this->client->request($method, "{$this->version}/$endpoint", $options);
    } catch (RequestException $error) {
      $json = json_decode($error->getResponse()->getBody()->getContents());

      if (!$json) {
        throw $error;
      }

      Log::error("OpenAI API request failed", [
        'error' => $error,
        'response' => $json,
        'trace' => $error->getTrace(),
      ]);

      $json->error->message = $json->error->message ?? 'An error occurred while processing your request';
      $json->error->message = "OpenAI API Error: {$json->error->message}";

      if ($json->error->code === 'context_length_exceeded') {
        throw new ContextLengthExceededException($json->error->message);
      } elseif ($json->error->code === 'rate_limit_exceeded') {
        throw new RateLimitExceededException($json->error->message);
      } elseif ($json->error->type === 'invalid_request_error') {
        throw new InvalidRequestException($json->error->message);
      } else {
        throw ControllerException::make($json->error->message, 500);
      }
    }

    if ($response->getStatusCode() !== 200) {
      throw new Exception("Error Processing Request");
    }

    if (isset($options['stream']) && $options['stream'] === true) {
      return $response->getBody();
    }

    $body = $response->getBody()->getContents();
    $json = json_decode($body);

    return $json;
  }
}
