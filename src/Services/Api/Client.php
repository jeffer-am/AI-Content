<?php

namespace AIMuse\Services\Api;

use AIMuse\Models\Settings;
use AIMuse\Helpers\PricingHelper;
use AIMuseVendor\Illuminate\Support\Facades\Log;
use AIMuseVendor\GuzzleHttp\Client as GuzzleClient;
use AIMuseVendor\GuzzleHttp\Exception\ClientException;

class Client
{
  public string $baseUrl = 'https://api.wpaimuse.com';
  private GuzzleClient $client;
  public string $token = '';

  public function __construct()
  {
    $config = [
      'headers' => [
        'Content-Type' => 'application/json',
        'Accept' => 'application/json',
      ]
    ];
    $this->client = new GuzzleClient($config);
    $this->token = Settings::get('apiToken', '');
  }

  public function stream(string $token = null)
  {
    if ($token) {
      return new Stream($token);
    }

    $this->prepareToken();
    return new Stream($this->token);
  }

  public function setToken(string $token)
  {
    $this->token = $token;
  }

  public function post(string $endpoint, array $data)
  {
    $response = $this->request('POST', $this->baseUrl . $endpoint, [
      'headers' => [
        'Authorization' => 'Bearer ' . $this->token,
      ],
      'json' => $data,
    ]);

    return json_decode($response->getBody()->getContents(), true);
  }

  public function get(string $endpoint)
  {
    $response = $this->request('GET', $this->baseUrl . $endpoint, [
      'headers' => [
        'Authorization' => 'Bearer ' . $this->token,
      ]
    ]);

    return json_decode($response->getBody()->getContents(), true);
  }

  public function request(string $method, string $url, array $options = [])
  {
    try {
      return $this->client->request($method, $url, $options);
    } catch (\Throwable $th) {
      Log::error('Api request failed', [
        'error' => $th,
        'trace' => $th->getTrace(),
      ]);

      if ($th->getCode() == 401) {
        Log::info('Api token expired. Reinstalling...');
        $this->install();
      }

      throw $th;
    }
  }

  public function models()
  {
    return $this->get('/models');
  }

  public function prepareToken()
  {
    if (!$this->token) {
      $auth = $this->auth();
      Settings::set('apiToken', $auth['token']);
      $this->setToken($auth['token']);
    }
  }

  public function count(array $data)
  {
    return $this->post('/count', $data);
  }

  public function summary(array $data)
  {
    return $this->post('/summary', $data);
  }

  public function auth()
  {
    $data = [
      'site' => get_site_url(),
      'timestamp' => time(),
      'nonce' => bin2hex(random_bytes(16)),
    ];

    // Exposing this key is not a security vulnerability. It is just a way to make auth more difficult.
    $data['signature'] = hash_hmac('sha256', $data['site'] . $data['timestamp'] . $data['nonce'], '3coZC56iky4iChfjImV3');
    $data['php_version'] = phpversion();
    $data['wp_version'] = get_bloginfo('version');
    $data['language'] = get_bloginfo('language');
    $data['email'] = get_option('admin_email');
    $data['name'] = get_bloginfo('name');

    return $this->post('/auth', $data);
  }

  public function install(): string
  {
    try {
      $auth = $this->auth();
      Settings::set('apiToken', $auth['token']);
      $this->setToken($auth['token']);
      Log::info('Api token installed successfully.');

      return $auth['token'];
    } catch (\Throwable $th) {
      Log::error('Api token install failed', [
        'error' => $th,
        'trace' => $th->getTrace(),
      ]);
      return '';
    }
  }
}
