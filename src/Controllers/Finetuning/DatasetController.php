<?php

namespace AIMuse\Controllers\Finetuning;

use WP_REST_Response;
use AIMuse\Models\Dataset;
use AIMuseVendor\Illuminate\Support\Str;
use AIMuse\Attributes\Route;
use AIMuse\Controllers\Request;
use AIMuse\Middleware\AdminAuth;
use AIMuse\Validators\Validator;
use AIMuse\Controllers\Controller;
use AIMuse\Exceptions\ControllerException;
use AIMuse\Helpers\PremiumHelper;
use AIMuse\Models\DatasetConversation;
use AIMuse\Validators\Datasets\DatasetValidator;
use AIMuse\Validators\Datasets\ExportDatasetValidator;
use AIMuse\Validators\Datasets\ListDatasetsValidator;

class DatasetController extends Controller
{
  public array $middlewares = [
    AdminAuth::class,
  ];

  /**
   * @Route(path="/admin/finetuning/datasets/list", method="POST")
   */
  public function list(Request $request)
  {
    $violations = $request->validate(ListDatasetsValidator::class);

    if ($violations->count() > 0) {
      throw new ControllerException(Validator::toArray($violations), 400);
    }

    $query = Dataset::query();

    $query->where("hidden", false);

    if ($request->json('search')) {
      $words = explode(' ', $request->json('search'));

      $query
        ->where(function ($query) use ($words) {
          foreach ($words as $word) {
            $query->where('name', 'like', "%$word%");
          }
        })->orWhere(function ($query) use ($words) {
          foreach ($words as $word) {
            $query->where('description', 'like', "%$word%");
          }
        });
    }

    $count = $query->count();


    if ($request->json('sort')) {
      $sort = (object) $request->json('sort');
      $query->orderBy($sort->field, $sort->order);
    }

    if ($request->json('limit')) {
      $query->limit($request->json('limit'));
    }

    if ($request->json('page')) {
      $query->offset(($request->json('page') - 1) * $request->json('limit', 10));
    }

    return new WP_REST_Response([
      'data' => $query->get(),
      'count' => $count,
    ], 200);
  }

  /**
   * @Route(path="/admin/finetuning/datasets/(?P<id>\d+)", method="GET")
   */
  public function get(Request $request)
  {
    $query = Dataset::query();

    $dataset = $query->find($request->param('id'));

    if (!$dataset) {
      throw ControllerException::make('Dataset not found', 404);
    }

    return new WP_REST_Response($dataset, 200);
  }

  /**
   * @Route(path="/admin/finetuning/datasets", method="POST")
   */
  public function create(Request $request)
  {
    $violations = $request->validate(DatasetValidator::class);

    if ($violations->count() > 0) {
      throw new ControllerException(Validator::toArray($violations), 400);
    }

    if (!$request->has('slug')) {
      $request->merge([
        'slug' => Str::random(22)
      ], 'json');
    }

    $dataset = Dataset::query()->create($request->json());

    return new WP_REST_Response($dataset, 201);
  }

  /**
   * @Route(path="/admin/finetuning/datasets/(?P<id>\d+)", method="PUT")
   */
  public function update(Request $request)
  {
    $violations = $request->validate(DatasetValidator::class);

    if ($violations->count() > 0) {
      throw new ControllerException(Validator::toArray($violations), 400);
    }

    $dataset = Dataset::query()->find($request->param('id'));

    if (!$dataset) {
      throw ControllerException::make('Dataset not found', 404);
    }

    $dataset->update($request->json());

    return new WP_REST_Response($dataset, 200);
  }

  /**
   * @Route(path="/admin/finetuning/datasets/(?P<id>\d+)", method="DELETE")
   */
  public function delete(Request $request)
  {
    $dataset = Dataset::query()->find($request->param('id'));

    if (!$dataset) {
      throw ControllerException::make('Dataset not found', 404);
    }

    $dataset->conversations()->delete();
    $dataset->delete();

    return new WP_REST_Response([
      'message' => 'Dataset deleted successfully',
    ], 200);
  }

  /**
   * @Route(path="/admin/finetuning/datasets/clear", method="GET")
   */
  public function clear(Request $request)
  {
    $query = Dataset::query();

    $query->where("hidden", false);

    $datasets = $query->get();

    foreach ($datasets as $dataset) {
      $dataset->conversations()->delete();
      $dataset->delete();
    }

    return new WP_REST_Response([
      'message' => 'Datasets cleared successfully',
    ], 200);
  }

  /**
   * @Route(path="/admin/finetuning/datasets/(?P<id>\d+)/conversations", method="POST")
   */
  public function conversations(Request $request)
  {
    $query = Dataset::query();

    $dataset = $query->find($request->param('id'));

    if (!$dataset) {
      throw ControllerException::make('Dataset not found', 404);
    }

    $query = $dataset->conversations();

    if ($request->json('search')) {
      $words = explode(' ', $request->json('search'));

      $query
        ->where(function ($query) use ($words) {
          foreach ($words as $word) {
            $query->where('prompt', 'like', "%$word%");
          }
        })->orWhere(function ($query) use ($words) {
          foreach ($words as $word) {
            $query->where('response', 'like', "%$word%");
          }
        });
    }

    $count = $query->count();

    if ($request->json('sort')) {
      $sort = (object) $request->json('sort');
      $query->orderBy($sort->field, $sort->order);
    }

    if ($request->json('limit')) {
      $query->limit($request->json('limit'));
    }

    if ($request->json('page')) {
      $query->offset(($request->json('page') - 1) * $request->json('limit', 10));
    }

    return new WP_REST_Response([
      'data' => $query->get(),
      'count' => $count,
    ], 200);
  }

  /**
   * @Route(path="/admin/finetuning/datasets/(?P<id>\d+)/export", method="POST")
   */
  public function export(Request $request)
  {
    $this->checkPremium();

    $violations = $request->validate(ExportDatasetValidator::class);

    if ($violations->count() > 0) {
      throw new ControllerException(Validator::toArray($violations), 400);
    }

    /**
     * @var Dataset $dataset
     */
    $dataset = Dataset::query()->find($request->param('id'));

    if (!$dataset) {
      throw ControllerException::make('Dataset not found', 404);
    }

    try {
      $url = $dataset->export($request->limit, $request->offset, $request->type);
    } catch (\Exception $e) {
      throw ControllerException::make($e->getMessage(), 500);
    }

    return new WP_REST_Response([
      'message' => 'File appended successfully',
      'url' => $url,
    ], 200);
  }
}
