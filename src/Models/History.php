<?php

namespace AIMuse\Models;

use AIMuse\Database\Model;

class History extends Model
{
  protected $table = 'aimuse_history';
  protected $guarded = [];

  protected $casts = [
    'price' => 'float',
    'tokens' => 'int',
    'data' => 'array',
  ];

  public function user()
  {
    return $this->belongsTo(User::class, 'user_id');
  }
}
