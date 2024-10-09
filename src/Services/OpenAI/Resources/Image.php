<?php

namespace AIMuse\Services\OpenAI\Resources;

use AIMuse\Contracts\Transporter;
use AIMuse\Helpers\PricingHelper;
use AIMuse\Models\History;
use AIMuse\Models\Settings;
use AIMuse\Services\OpenAI\OpenAI;
use AIMuse\Services\OpenAI\Responses\ImageResponse;

class Image
{
  private Transporter $transporter;

  public function __construct(Transporter $transporter)
  {
    $this->transporter = $transporter;
  }

  public function create($options, string $component)
  {
    $options['response_format'] = 'b64_json';
    $response = $this->transporter->post("images/generations", $options);

    $price = PricingHelper::image([
      'model' => $options['model'],
      'service' => 'openai',
      'quality' => $options['quality'] ?? 'standard',
      'resolution' => $options['size'] ?? '1024x1024',
    ]);

    History::query()->create([
      'user_id' => get_current_user_id(),
      'model' => $options['model'],
      'service' => 'openai',
      'component' => $component,
      'tokens' => 1,
      'price' => $price,
    ]);

    return [
      'base64' => $response->data[0]->b64_json,
      'price' => $price,
    ];
  }
}
