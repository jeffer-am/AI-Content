<?php

namespace AIMuse\Controllers;

use AIMuse\Attributes\Route;
use AIMuse\Middleware\AdminAuth;
use AIMuse\Controllers\Controller;
use AIMuse\Models\Settings;
use WP_REST_Response;

class SystemController extends Controller
{
  public array $middlewares = [
    AdminAuth::class,
  ];

  /**
   * @Route(path="/admin/clear", method="GET")
   */
  public function clear()
  {
    aimuse()->uninstall();
    deactivate_plugins([aimuse()->file()]);

    return new WP_REST_Response([
      'message' => 'Plugin cleared successfully',
      'redirect' => admin_url('plugins.php')
    ], 200);
  }

  /**
   * @Route(path="/admin/reset", method="GET")
   */
  public function reset()
  {
    Settings::clearCache();
    aimuse()->uninstall();
    aimuse()->install();
    Settings::set('acceptedTerms', true);

    return new WP_REST_Response([
      'message' => 'Plugin reset successfully',
    ], 200);
  }

  /**
   * @Route(path="/admin/repair", method="GET")
   */
  public function repair()
  {
    aimuse()->install();

    return new WP_REST_Response([
      'message' => 'Plugin repaired successfully',
    ], 200);
  }

  /**
   * @Route(path="/admin/tests/stream", method="GET")
   */
  public function stream()
  {
    header('Content-Type: text/event-stream');
    header('Cache-Control: no-cache');
    header('X-Accel-Buffering: no');
    ob_implicit_flush(true);

    for ($i = 0; $i < 3; $i++) {
      if (connection_aborted()) {
        break;
      }
      echo 'data: ok' . "\n";
      if (ob_get_level() > 0) {
        ob_flush();
      }
      flush();
      sleep(1);
    }

    echo 'data: done';
    if (ob_get_level() > 0) {
      ob_flush();
    }
    flush();
    exit();
  }
}
