<?php

namespace AIMuse\Models;

use AIMuse\Database\Model;

class PlaygroundMessage extends Model
{
  protected $table = 'aimuse_playground_messages';
  protected $guarded = [];
  protected $casts = [
    'meta' => 'array',
  ];

  public function playground()
  {
    return $this->belongsTo(PlaygroundChat::class, 'chat_id');
  }
}
