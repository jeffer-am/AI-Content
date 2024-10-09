<?php

namespace AIMuse\Models;

use AIMuse\Database\Model;

class DatasetConversation extends Model
{
  protected $table = 'aimuse_dataset_conversations';
  protected $guarded = [];

  public function dataset()
  {
    return $this->belongsTo(Dataset::class, 'dataset_id');
  }

  public function toJsonLine(): string
  {
    $row = [
      'messages' => [
        ['role' => 'user', 'content' => $this->prompt],
        ['role' => 'assistant', 'content' => $this->response]
      ]
    ];

    return json_encode($row) . PHP_EOL;
  }

  public function toCsv(): string
  {
    $this->prompt = str_replace('"', '""', $this->prompt);
    $this->prompt = str_replace("\n", "\\n", $this->prompt);
    $this->prompt = str_replace("\r", "\\r", $this->prompt);

    $this->response = str_replace('"', '""', $this->response);
    $this->response = str_replace("\n", "\\n", $this->response);
    $this->response = str_replace("\r", "\\r", $this->response);

    return '"' . $this->prompt . '","' . $this->response . '"' . PHP_EOL;
  }

  public static function fromJsonLine(string $line): self
  {
    $row = json_decode($line, true);

    $conversation = new self();
    $conversation->prompt = $row['messages'][0]['content'];
    $conversation->response = $row['messages'][1]['content'];

    return $conversation;
  }
}
