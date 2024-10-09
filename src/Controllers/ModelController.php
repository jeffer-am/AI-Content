<?php

namespace AIMuse\Controllers;

use WP_REST_Response;
use AIMuse\Models\AIModel;
use AIMuse\Models\Settings;
use AIMuse\Attributes\Route;
use AIMuse\Middleware\AdminAuth;
use AIMuse\Validators\Validator;
use AIMuse\Controllers\Controller;
use AIMuse\Exceptions\ControllerException;
use AIMuseVendor\Illuminate\Support\Facades\Log;
use AIMuse\Validators\UpdateModelValidator;
use AIMuse\Wordpress\Schedules\ModelsSyncSchedule;

class ModelController extends Controller
{
  public array $middlewares = [
    AdminAuth::class,
  ];

  /**
   * @Route(path="/admin/models", method="GET")
   */
  public function list()
  {
    $models = AIModel::orderBy("name", "asc")->get();

    return new WP_REST_Response($models);
  }

  /**
   * @Route(path="/admin/models", method="POST")
   */
  public function update(Request $request)
  {
    $violations = $request->validate(UpdateModelValidator::class);

    if ($violations->count() > 0) {
      return new WP_REST_Response([
        'errors' => Validator::toArray($violations),
      ], 400);
    }

    $model = AIModel::find($request->json('id'));

    if (!$model) {
      return new WP_REST_Response(
        [
          'errors' => [
            'message' => 'Model not found',
          ]
        ],
        400
      );
    }

    $model->update($request->json());

    return new WP_REST_Response([
      'message' => 'Model updated successfully',
    ]);
  }

  /**
   * @Route(path="/admin/models/sync", method="GET")
   */
  public function sync()
  {
    try {
      $models = AIModel::sync();

      Log::info('Models were successfully synchronized manually.');

      return new WP_REST_Response([
        'message' => 'Models synced successfully.',
      ]);
    } catch (\Throwable $th) {
      Log::error('Model synchronization failed', [
        'error' => $th,
        'trace' => $th->getTrace(),
        'models' => $models,
      ]);

      throw new ControllerException([
        [
          'message' => 'Failed to sync models',
        ]
      ], 400);
    }
  }
}
