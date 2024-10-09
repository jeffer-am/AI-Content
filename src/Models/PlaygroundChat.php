<?php

namespace AIMuse\Models;

use AIMuse\Database\Model;

class PlaygroundChat extends Model
{
  protected $table = 'aimuse_playground_chats';
  protected $guarded = [];

  protected $casts = [
    'meta' => 'array',
  ];

  public function messages()
  {
    return $this->hasMany(PlaygroundMessage::class, 'chat_id');
  }
}
