<?php

namespace AIMuse\Controllers\Finetuning;

use WP_REST_Response;
use AIMuse\Models\Dataset;
use AIMuse\Attributes\Route;
use AIMuse\Controllers\Request;
use AIMuse\Middleware\AdminAuth;
use AIMuse\Validators\Validator;
use AIMuse\Controllers\Controller;
use AIMuse\Models\DatasetConversation;
use AIMuse\Exceptions\ControllerException;
use AIMuse\Validators\Datasets\Conversations\CreateConversationValidator;
use AIMuse\Validators\Datasets\Conversations\UpdateConversationValidator;
use AIMuse\Validators\Datasets\Conversations\CreateBulkConversationValidator;

class DatasetConversationController extends Controller
{
  public array $middlewares = [
    AdminAuth::class,
  ];

  /**
   * @Route(path="/admin/finetuning/datasets/conversations", method="GET")
   */
  public function list()
  {
    $conversations = DatasetConversation::query()->get();

    return new WP_REST_Response($conversations, 200);
  }

  /**
   * @Route(path="/admin/finetuning/datasets/conversations", method="POST")
   */
  public function create(Request $request)
  {
    $violations = $request->validate(CreateConversationValidator::class);

    if ($violations->count() > 0) {
      throw new ControllerException(Validator::toArray($violations), 400);
    }

    $dataset = Dataset::query()->find($request->json('dataset_id'));

    if (!$dataset) {
      throw ControllerException::make('Dataset not found', 404);
    }

    $conversation = $dataset->conversations()->create($request->json());

    return new WP_REST_Response($conversation, 201);
  }

  /**
   * @Route(path="/admin/finetuning/datasets/(?P<dataset>[a-zA-z0-9-_]+)/conversations/bulk", method="POST")
   */
  public function bulkCreate(Request $request)
  {
    $violations = $request->validate(CreateBulkConversationValidator::class);

    if ($violations->count() > 0) {
      throw new ControllerException(Validator::toArray($violations), 400);
    }

    $dataset = Dataset::query()->where('slug', $request->param('dataset'))->first();

    if (!$dataset) {
      throw ControllerException::make('Dataset not found', 404);
    }

    $conversations = $request->json('conversations');

    foreach ($conversations as &$conversation) {
      $conversation['created_at'] = current_time('mysql');
      $conversation['updated_at'] = current_time('mysql');
      $conversation['dataset_id'] = $dataset->id;
    }

    DatasetConversation::query()->insert($conversations);

    return new WP_REST_Response([
      'message' => 'Conversations created successfully',
    ], 201);
  }

  /**
   * @Route(path="/admin/finetuning/datasets/conversations/(?P<id>\d+)", method="PUT")
   */
  public function update(Request $request)
  {
    $violations = $request->validate(UpdateConversationValidator::class);

    if ($violations->count() > 0) {
      throw new ControllerException(Validator::toArray($violations), 400);
    }

    $conversation = DatasetConversation::query()->find($request->param('id'));

    if (!$conversation) {
      throw ControllerException::make('Conversation not found', 404);
    }

    $conversation->update($request->json());

    return new WP_REST_Response($conversation, 200);
  }

  /**
   * @Route(path="/admin/finetuning/datasets/conversations/(?P<id>\d+)", method="DELETE")
   */
  public function delete(Request $request)
  {
    $conversation = DatasetConversation::query()->find($request->param('id'));

    if (!$conversation) {
      throw ControllerException::make('Conversation not found', 404);
    }

    $conversation->delete();

    return new WP_REST_Response([
      'message' => 'Conversation deleted successfully',
    ], 200);
  }

  /**
   * @Route(path="/admin/finetuning/datasets/(?P<dataset>\w+)/conversations/clear", method="GET")
   */
  public function clear(Request $request)
  {
    $dataset = Dataset::query()->find($request->param('dataset'));

    if (!$dataset) {
      throw ControllerException::make('Dataset not found', 404);
    }

    $dataset->conversations()->delete();

    return new WP_REST_Response([
      'message' => 'Conversations cleared successfully',
    ], 200);
  }
}
