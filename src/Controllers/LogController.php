<?php

namespace AIMuse\Controllers;

use WP_REST_Response;
use AIMuse\Attributes\Route;
use AIMuse\Middleware\AdminAuth;
use AIMuse\Controllers\Controller;
use AIMuse\Exceptions\ControllerException;

class LogController extends Controller
{
  public array $middlewares = [
    AdminAuth::class,
  ];

  /**
   * @Route(path="/admin/logs", method="POST")
   */
  public function list(Request $request)
  {
    $file = aimuse()->logPath(date('Y-m-d', $request->date));
    $logs = file_exists($file) ? file_get_contents($file) : '';
    $logs = trim($logs);
    $logs = explode("\n", $logs);
    $logs = array_reverse($logs);

    return new WP_REST_Response($logs);
  }

  /**
   * @Route(path="/admin/logs/clear", method="POST")
   */
  public function clear()
  {
    $blogId = get_current_blog_id();
    $dir = dirname(aimuse()->logPath());
    $files = glob("$dir/blog-$blogId-*");

    foreach ($files as $file) {
      unlink($file);
    }

    return new WP_REST_Response([
      'message' => 'Logs cleared',
    ]);
  }

  /**
   * @Route(path="/admin/logs/settings", method="POST")
   */
  public function setSettings(Request $request)
  {
    $settings = $request->json();
    update_option('aimuse_logs', $settings);

    return new WP_REST_Response([
      'message' => 'Settings updated',
    ]);
  }

  /**
   * @Route(path="/admin/logs/settings", method="GET")
   */
  public function getSettings()
  {
    $settings = get_option('aimuse_logs', [
      'level' => 'info',
      'days' => 7,
    ]);

    return new WP_REST_Response($settings);
  }
}
