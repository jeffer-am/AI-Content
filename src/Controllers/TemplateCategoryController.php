<?php

namespace AIMuse\Controllers;

use WP_REST_Response;
use AIMuse\Models\Template;
use AIMuse\Attributes\Route;
use AIMuse\Middleware\AdminAuth;
use AIMuse\Validators\Validator;
use AIMuse\Models\TemplateCategory;
use AIMuse\Exceptions\ControllerException;
use AIMuse\Validators\Templates\Categories\CreateValidator;
use AIMuse\Validators\Templates\Categories\DeleteValidator;
use AIMuse\Validators\Templates\Categories\UpdateValidator;

class TemplateCategoryController extends Controller
{
  public array $middlewares = [
    AdminAuth::class
  ];

  /**
   * @Route(path="/admin/templates/categories", method="GET")
   */
  public function list(Request $request)
  {
    return TemplateCategory::query()->with('templates')->get();
  }

  /**
   * @Route(path="/admin/templates/categories", method="POST")
   */
  public function create(Request $request)
  {
    $violations = $request->validate(CreateValidator::class);

    if ($violations->count() > 0) {
      throw new ControllerException(Validator::toArray($violations), 400);
    }

    $category = TemplateCategory::query()->create([
      'name' => $request->name,
    ]);

    return $category;
  }

  /**
   * @Route(path="/admin/templates/categories", method="PUT")
   */
  public function update(Request $request)
  {
    $violations = $request->validate(UpdateValidator::class);

    if ($violations->count() > 0) {
      throw new ControllerException(Validator::toArray($violations), 400);
    }

    $category = TemplateCategory::query()->find($request->id);

    if (!$category) {
      throw new ControllerException([
        [
          'message' => 'Category not found'
        ]
      ], 400);
    }

    $category->update([
      'name' => $request->name,
    ]);

    return $category;
  }

  /**
   * @Route(path="/admin/templates/categories", method="DELETE")
   */
  public function delete(Request $request)
  {
    $violations = $request->validate(DeleteValidator::class);

    if ($violations->count() > 0) {
      throw new ControllerException(Validator::toArray($violations), 400);
    }

    $category = TemplateCategory::query()->find($request->id);

    if (!$category) {
      throw new ControllerException([
        [
          'message' => 'Category not found'
        ]
      ], 400);
    }

    $category->templates()->delete();
    $category->delete();

    return [
      'message' => 'Category deleted'
    ];
  }
}
