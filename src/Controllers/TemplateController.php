<?php

namespace AIMuse\Controllers;

use WP_REST_Response;
use AIMuse\Models\Template;
use AIMuseVendor\Illuminate\Support\Str;
use AIMuse\Attributes\Route;
use AIMuse\Validators\Validator;
use AIMuse\Models\TemplateCategory;
use AIMuse\Exceptions\ControllerException;
use AIMuse\Middleware\Editor;
use AIMuse\Validators\Templates\CreateValidator;
use AIMuse\Validators\Templates\DeleteValidator;
use AIMuse\Validators\Templates\UpdateValidator;

class TemplateController extends Controller
{
  public array $middlewares = [
    Editor::class
  ];

  /**
   * @Route(path="/admin/templates/list", method="POST")
   */
  public function list(Request $request)
  {
    $query = Template::query()->with('dataset');

    if ($request->has('slugs')) {
      $query->whereIn('slug', $request->slugs);
    } elseif ($request->has('ids')) {
      $query->whereIn('id', $request->ids);
    }

    if ($request->has('type')) {
      $query->where('type', $request->type);
    }

    $templates = $query->get();
    $templates->append('restorable');

    return $templates;
  }

  /**
   * @Route(path="/admin/templates", method="POST")
   */
  public function create(Request $request)
  {
    $this->checkPremium();

    $violations = $request->validate(CreateValidator::class);

    if ($violations->count() > 0) {
      throw new ControllerException(Validator::toArray($violations), 400);
    }

    $category = null;

    if ($request->has('category_id')) {
      $category = TemplateCategory::query()->find($request->category_id);
    } else {
      $category = TemplateCategory::query()->firstOrCreate([
        'name' => 'Uncategorized'
      ]);
    }

    if (!$category) {
      throw new ControllerException([
        [
          'message' => 'Category not found'
        ]
      ], 400);
    }

    if (!$request->has('capabilities')) {
      $request->merge([
        'capabilities' => []
      ]);
    }

    if (!$request->has('slug')) {
      $request->merge([
        'slug' => Str::random(16)
      ]);
    } else {
      $exists = Template::query()->where('slug', $request->slug)->exists();

      if ($exists) {
        throw new ControllerException([
          [
            'message' => 'Slug already exists'
          ]
        ], 400);
      }
    }

    $template = $category->templates()->create($request->except(Template::$excepts));

    return $template;
  }

  /**
   * @Route(path="/admin/templates", method="PUT")
   */
  public function update(Request $request)
  {
    $this->checkPremium();

    $violations = $request->validate(UpdateValidator::class);

    if ($violations->count() > 0) {
      throw new ControllerException(Validator::toArray($violations), 400);
    }

    $category = TemplateCategory::query()->find($request->category_id);

    if (!$category) {
      throw new ControllerException([
        [
          'message' => 'Category not found'
        ]
      ], 400);
    }

    $template = Template::query()->find($request->id);

    if (!$template) {
      throw new ControllerException([
        [
          'message' => 'Template not found'
        ]
      ], 400);
    }

    $template->update($request->except(Template::$excepts));

    return $template;
  }

  /**
   * @Route(path="/admin/templates", method="DELETE")
   */
  public function delete(Request $request)
  {
    $this->checkPremium();

    $violations = $request->validate(DeleteValidator::class);

    if ($violations->count() > 0) {
      throw new ControllerException(Validator::toArray($violations), 400);
    }

    $template = Template::query()->find($request->id);

    if (!$template) {
      throw new ControllerException([
        [
          'message' => 'Template not found'
        ]
      ], 400);
    }

    $deleted = $template->delete();

    return [
      'deleted' => $deleted
    ];
  }

  /**
   * @Route(path="/admin/templates/restore", method="POST")
   */
  public function restore(Request $request)
  {
    $this->checkPremium();

    $template = Template::query()->find($request->id);

    if (!$template) {
      throw new ControllerException([
        [
          'message' => 'Template not found'
        ]
      ], 400);
    }

    $restorable = $template->restorable;

    if (!$restorable) {
      throw new ControllerException([
        [
          'message' => 'Template is not restorable'
        ]
      ], 400);
    }

    $template->restore();

    return $template;
  }
}
