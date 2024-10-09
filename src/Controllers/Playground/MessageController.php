<?php

namespace AIMuse\Controllers\Playground;

use WP_REST_Response;
use AIMuse\Models\User;
use AIMuse\Attributes\Route;
use AIMuse\Controllers\Request;
use AIMuse\Middleware\AdminAuth;
use AIMuse\Validators\Validator;
use AIMuse\Controllers\Controller;
use AIMuse\Exceptions\ControllerException;
use AIMuse\Validators\Playground\CreateMessageValidator;
use AIMuse\Validators\Playground\UpdateMessageValidator;

class MessageController extends Controller
{
  public array $middlewares = [
    AdminAuth::class,
  ];

  /**
   * @Route(path="/admin/playground/chats/(?P<chat>\d+)/messages", method="POST")
   */
  public function create(Request $request)
  {
    $violations = $request->validate(CreateMessageValidator::class);

    if ($violations->count() > 0) {
      throw new ControllerException(Validator::toArray($violations), 400);
    }

    $user = User::query()->find(wp_get_current_user()->ID);
    $chat = $user->playgroundChats()->find($request->param('chat'));

    if (!$chat) {
      throw ControllerException::make('Chat not found', 404);
    }

    $lastMessage = $chat->messages()->latest()->first();

    if ($lastMessage && $lastMessage->role === $request->role) {
      throw ControllerException::make('You cannot send two messages in a row', 400);
    }

    $message = $chat->messages()->create([
      'content' => $request->content,
      'role' => $request->role,
      'meta' => $request->meta,
    ]);

    return new WP_REST_Response($message, 201);
  }

  /**
   * @Route(path="/admin/playground/chats/(?P<chat>\d+)/messages", method="GET")
   */
  public function list(Request $request)
  {
    $user = User::query()->find(wp_get_current_user()->ID);
    $chat = $user->playgroundChats()->find($request->param('chat'));

    if (!$chat) {
      throw ControllerException::make('Chat not found', 404);
    }

    $messages = $chat->messages()->get();

    return new WP_REST_Response($messages, 200);
  }

  /**
   * @Route(path="/admin/playground/chats/(?P<chat>\d+)/messages/(?P<message>\d+)", method="DELETE")
   */
  public function delete(Request $request)
  {
    $user = User::query()->find(wp_get_current_user()->ID);
    $chat = $user->playgroundChats()->find($request->param('chat'));

    if (!$chat) {
      throw ControllerException::make('Chat not found', 404);
    }

    $message = $chat->messages()->find($request->param('message'));

    if (!$message) {
      throw ControllerException::make('Message not found', 404);
    }

    $message->delete();

    return new WP_REST_Response([
      'message' => 'Message deleted successfully',
    ], 200);
  }

  /**
   * @Route(path="/admin/playground/chats/(?P<chat>\d+)/messages/(?P<message>\d+)", method="PUT")
   */
  public function update(Request $request)
  {
    $violations = $request->validate(UpdateMessageValidator::class);

    if ($violations->count() > 0) {
      throw new ControllerException(Validator::toArray($violations), 400);
    }

    $user = User::query()->find(wp_get_current_user()->ID);
    $chat = $user->playgroundChats()->find($request->param('chat'));

    if (!$chat) {
      throw ControllerException::make('Chat not found', 404);
    }

    $message = $chat->messages()->find($request->param('message'));

    if (!$message) {
      throw ControllerException::make('Message not found', 404);
    }

    $message->update($request->json());

    return new WP_REST_Response($message, 200);
  }

  /**
   * @Route(path="/admin/playground/chats/(?P<chat>\d+)/messages/clear", method="GET")
   */
  public function clear(Request $request)
  {
    $user = User::query()->find(wp_get_current_user()->ID);
    $chat = $user->playgroundChats()->find($request->param('chat'));

    if (!$chat) {
      throw ControllerException::make('Chat not found', 404);
    }

    $chat->messages()->delete();

    return new WP_REST_Response([
      'message' => 'Messages cleared successfully',
    ], 200);
  }
}
