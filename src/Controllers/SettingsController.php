<?php

namespace AIMuse\Controllers;

use AIMuse\Attributes\Route;
use AIMuse\Middleware\AdminAuth;
use AIMuse\Controllers\Controller;
use AIMuse\Controllers\Request;
use AIMuse\Exceptions\ControllerException;
use AIMuse\Helpers\PremiumHelper;
use AIMuse\Models\AIModel;
use AIMuse\Models\Settings;
use AIMuse\Models\Template;
use AIMuse\Validators\SettingsValidator;
use AIMuse\Validators\Validator;
use WP_REST_Response;

class SettingsController extends Controller
{
  public array $middlewares = [
    AdminAuth::class,
  ];

  /**
   * @Route(path="/admin/settings/fetch", method="POST")
   */
  public function fetch(Request $request)
  {
    $query = Settings::query()->select(['name', 'data']);

    if ($request->has('fields')) {
      $query->whereIn('name', $request->fields);
    }

    return Settings::prettify($query->get());
  }

  /**
   * @Route(path="/admin/settings", method="POST")
   */
  public function post(Request $request)
  {
    $violations = $request->validate(SettingsValidator::class);

    if ($violations->count() > 0) {
      throw new ControllerException(Validator::toArray($violations), 400);
    }

    $settings = $request->json();

    if (!PremiumHelper::isPremium()) {
      $premiumApiKeyNames = PremiumHelper::getPremiumApiKeyNames();
      $settings = array_diff_key($settings, array_flip($premiumApiKeyNames));

      if (isset($settings['textModel'])) {
        $model = AIModel::find($settings['textModel']);

        if (!$model) {
          throw new ControllerException([
            [
              'message' => 'Invalid model selected',
            ]
          ], 400);
        }

        if (PremiumHelper::serviceIsPremium($model->service)) {
          throw new ControllerException([
            [
              'message' => "You need to upgrade to premium to use {$model->service} service",
            ]
          ], 400);
        }
      }
    }

    foreach ($settings as $name => $value) {
      Settings::set($name, $value);
    }

    return [
      "status" => "success",
      "message" => "Settings saved successfully",
    ];
  }

  private function getSecretKey()
  {
    return substr(hash('sha256', 'wp-aimuse-backup'), 0, 16);
  }

  /**
   * @Route(path="/admin/settings/export", method="GET")
   */
  public function export()
  {
    $this->checkOpenSSL();

    $backup = [
      'settings' => Settings::export(),
      'models' => AIModel::export(),
      'templates' => Template::export(),
    ];

    $file = wp_json_encode($backup);
    $key = $this->getSecretKey();
    $file = openssl_encrypt($file, 'aes-128-cbc', $key, 0, $key);

    return new WP_REST_Response([
      'message' => 'Backup created successfully',
      'file' => $file,
    ], 200);
  }

  /**
   * @Route(path="/admin/settings/import", method="POST")
   */
  public function import(Request $request)
  {
    $this->checkOpenSSL();

    $file = $request->json('file');
    $key = $this->getSecretKey();
    $file = openssl_decrypt($file, 'aes-128-cbc', $key, 0, $key);

    if (!$file) {
      throw new ControllerException([
        [
          'message' => 'Invalid backup file',
        ]
      ], 400);
    }

    $backup = json_decode($file, true);

    if (isset($backup['settings'])) {
      Settings::import($backup['settings']);
    }

    if (isset($backup['models'])) {
      AIModel::import($backup['models']);
    }

    if (isset($backup['templates'])) {
      Template::import($backup['templates']);
    }

    return new WP_REST_Response([
      'message' => 'Settings restored successfully',
      'success' => true,
    ], 200);
  }

  public function checkOpenSSL()
  {
    if (!function_exists('openssl_encrypt')) {
      throw new ControllerException([
        [
          'message' => 'OpenSSL extension is not enabled',
        ]
      ], 400);
    }
  }
}
