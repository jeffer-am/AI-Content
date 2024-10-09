<?php

namespace AIMuse\Controllers;

use Throwable;
use WP_REST_Request;
use WP_REST_Response;
use AIMuse\Controllers\Request;
use AIMuse\Helpers\PremiumHelper;
use AIMuseVendor\Illuminate\Support\Facades\Log;
use AIMuse\Exceptions\ControllerException;

class Controller
{
  public array $middlewares = [];

  public function __call($name, $arguments)
  {


    $name = ltrim($name, '_');

    if (!method_exists($this, $name)) {
      throw new \Exception("Method $name does not exist");
    }

    $request = null;
    foreach ($arguments as $argument) {
      if ($argument instanceof WP_REST_Request) {
        $request = new Request($argument);
        break;
      }
    }

    if ($request) {
      try {
        return $this->$name($request);
      } catch (ControllerException $error) {
        return new WP_REST_Response([
          'errors' => $error->getErrors()
        ], $error->getCode());
      } catch (Throwable $error) {
        Log::error($error->getMessage(), [
          'error' => $error,
          'trace' => $error->getTrace(),
        ]);

        $error = [
          'message' => 'Something went wrong',
        ];

        if (current_user_can('manage_options')) {
          $error['links'] = [
            [
              'label' => 'Check the logs',
              'url' => aimuse()->menu('#/settings/logs')
            ]
          ];
        }

        return new WP_REST_Response([
          'errors' => [
            $error
          ],
        ], 500);
      }

      exit();
    }
  }

  protected function checkPremium()
  {
    if (!PremiumHelper::isPremium()) {
      throw new ControllerException([
        [
          'message' => 'You need to upgrade to premium to use this feature',
          'links' => [
            [
              'label' => 'Upgrade now',
              'url' => "https://wpaimuse.com/#pricing"
            ]
          ]
        ]
      ], 400);
    }
  }
}
