<?php

namespace AIMuse\Models;

use AIMuse\Database\Model;

class TemplateCategory extends Model
{
  protected $table = 'aimuse_template_categories';
  protected $guarded = [];

  public function templates()
  {
    return $this->hasMany(Template::class, 'category_id');
  }
}
