<?php

namespace AIMuse\Controllers\Playground;

use WP_REST_Response;
use AIMuse\Models\User;
use AIMuseVendor\Illuminate\Support\Str;
use AIMuse\Attributes\Route;
use AIMuse\Controllers\Request;
use AIMuse\Middleware\AdminAuth;
use AIMuse\Validators\Validator;
use AIMuse\Controllers\Controller;
use AIMuse\Exceptions\ControllerException;
use AIMuse\Controllers\GenerateTextController;
use AIMuse\Models\PlaygroundChat;
use AIMuse\Validators\Playground\CreateChatValidator;
use AIMuse\Validators\Playground\SearchChatValidator;
use AIMuse\Validators\Playground\UpdateChatValidator;

class ChatController extends Controller
{
  public array $middlewares = [
    AdminAuth::class,
  ];

  /**
   * @Route(path="/admin/playground/chats", method="POST")
   */
  public function create(Request $request)
  {
    $violations = $request->validate(CreateChatValidator::class);

    if ($violations->count() > 0) {
      throw new ControllerException(Validator::toArray($violations), 400);
    }

    $user = User::query()->find(wp_get_current_user()->ID);

    /**
     * @var PlaygroundChat $created
     */
    $created = $user->playgroundChats()->create([
      'title' => Str::replace("\n", ' ', substr($request->message['content'], 0, 90)),
      'meta' => $request->meta ?? null,
    ]);

    $message = $created->messages()->create([
      'content' => $request->message['content'],
      'meta' => $request->message['meta'] ?? null,
      'role' => 'user',
    ]);

    $created->messages = [$message];

    return new WP_REST_Response($created, 201);
  }

  /**
   * @Route(path="/admin/playground/chats", method="GET")
   */
  public function list(Request $request)
  {
    $user = User::query()->find(wp_get_current_user()->ID);

    $query = $user->playgroundChats();

    if ($request->query('search')) {
      $words = explode(' ', $request->query('search'));

      foreach ($words as $word) {
        $query->where('title', 'LIKE', "%$word%");
      }
    }

    $chats = $query->orderBy('id', 'DESC')->get();

    return new WP_REST_Response($chats, 200);
  }

  /**
   * @Route(path="/admin/playground/chats/(?P<id>\d+)", method="GET")
   */
  public function get(Request $request)
  {
    $user = User::query()->find(wp_get_current_user()->ID);
    $chat = $user->playgroundChats()->with(['messages'])->find($request->param('id'));

    if (!$chat) {
      throw ControllerException::make('Chat not found', 404);
    }

    return new WP_REST_Response($chat, 200);
  }

  /**
   * @Route(path="/admin/playground/chats/(?P<id>\d+)", method="PUT")
   */
  public function update(Request $request)
  {
    $violations = $request->validate(UpdateChatValidator::class);

    if ($violations->count() > 0) {
      throw new ControllerException(Validator::toArray($violations), 400);
    }

    $user = User::query()->find(wp_get_current_user()->ID);
    $chat = $user->playgroundChats()->find($request->param('id'));

    if (!$chat) {
      throw ControllerException::make('Chat not found', 404);
    }

    if ($request->json('title')) {
      $chat->title = Str::replace("\n", ' ', substr($request->json('title'), 0, 90));
    }

    if ($request->json('meta')) {
      $chat->meta = $request->json('meta');
    }

    $chat->save();

    return new WP_REST_Response($chat, 200);
  }

  /**
   * @Route(path="/admin/playground/chats/(?P<id>\d+)", method="DELETE")
   */
  public function delete(Request $request)
  {
    $user = User::query()->find(wp_get_current_user()->ID);
    $chat = $user->playgroundChats()->find($request->param('id'));

    if (!$chat) {
      throw ControllerException::make('Chat not found', 404);
    }

    $chat->messages()->delete();
    $chat->delete();

    return new WP_REST_Response([
      'message' => 'Chat deleted successfully',
    ], 200);
  }

  /**
   * @Route(path="/admin/playground/chats/clear", method="GET")
   */
  public function clear()
  {
    $user = User::query()->find(wp_get_current_user()->ID);

    $user->playgroundChats()->each(function (PlaygroundChat $chat) {
      $chat->messages()->delete();
      $chat->delete();
    });

    return new WP_REST_Response([
      'message' => 'Chats cleared successfully',
    ], 200);
  }

  /**
   * @Route(path="/admin/playground/chats/(?P<id>\d+)/generate", method="POST")
   */
  public function generate(Request $request)
  {
    $user = User::query()->find(wp_get_current_user()->ID);
    $chat = $user->playgroundChats()->with('messages')->find($request->param('id'));

    if (!$chat) {
      throw ControllerException::make('Chat not found', 404);
    }

    $data = [
      'messages' => [],
    ];

    foreach ($chat->messages->slice(0, -1) as $message) {
      $data['messages'][] = [
        'content' => $message->content,
        'role' => $message->role,
      ];
    }

    $last = $chat->messages->last();
    $data['prompt'] = $last->content;

    if (isset($last->meta['templates'])) {
      $data['templates'] = $last->meta['templates'];
    }

    $request->merge($data, 'json');

    return (new GenerateTextController())->text($request);
  }
}
