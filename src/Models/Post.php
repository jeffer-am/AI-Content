<?php

namespace AIMuse\Models;

use AIMuse\Database\Model;

class Post extends Model
{
  protected $guarded = [];
  public $timestamps = false;
  protected $primaryKey = 'ID';
}
