<?php

namespace AIMuse\Models;

use AIMuse\Database\Model;

class User extends Model
{
  protected $guarded = [];
  public $timestamps = false;
  protected $primaryKey = 'ID';

  protected $visible = ['user_nicename', 'display_name', 'user_login', 'ID'];

  public function history()
  {
    return $this->hasMany(History::class, 'user_id');
  }

  public function playgroundChats()
  {
    return $this->hasMany(PlaygroundChat::class, 'user_id');
  }
}
