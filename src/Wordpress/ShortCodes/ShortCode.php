<?php

namespace AIMuse\Wordpress\ShortCodes;

use AIMuse\Exceptions\ShortCodeException;

class ShortCode
{
  protected string $name;
  protected array $scripts = [];
  protected array $styles = [];

  public function register()
  {
    add_shortcode($this->name, [$this, 'prepare']);
  }

  public function prepare()
  {
    foreach ($this->scripts as $script) {
      wp_enqueue_script($script, $script, [], false, true);
    }

    foreach ($this->styles as $style) {
      wp_enqueue_style($style, $style, [], false, true);
    }

    return $this->show();
  }

  public function show(array $attributes = [])
  {
    throw new ShortCodeException('Shortcode must implement show method');
  }
}
