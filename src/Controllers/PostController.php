<?php

namespace AIMuse\Controllers;

use AIMuse\Attributes\Route;
use AIMuse\Middleware\AdminAuth;
use AIMuse\Controllers\Controller;
use AIMuse\Exceptions\ControllerException;
use AIMuse\Models\Post;
use WP_REST_Response;

class PostController extends Controller
{
  public array $middlewares = [
    AdminAuth::class,
  ];

  /**
   * @Route(path="/admin/posts/(?P<id>\d+)", method="GET")
   */
  public function get(Request $request)
  {
    $post = Post::query()->find($request->param('id'));

    if (!$post) {
      throw new ControllerException([
        [
          'message' => 'Post not found'
        ]
      ], 404);
    }

    $post['edit_link'] = get_edit_post_link($post['ID'], 'none');

    return new WP_REST_Response($post);
  }

  /**
   * @Route(path="/admin/posts/(?P<id>\d+)", method="POST")
   */
  public function update(Request $request)
  {
    $post = array_merge($request->except(['id', 'terms']), [
      'ID' => $request->param('id')
    ]);

    $updated = wp_update_post($post);

    if (!$updated) {
      throw new ControllerException([
        [
          'message' => 'Failed to update post'
        ]
      ], 500);
    }

    $post = get_post($updated)->to_array();

    if ($request->has('terms')) {
      $post['terms'] = $this->setTerms($updated, $request->param('terms'));
    }

    $post['edit_link'] = get_edit_post_link($updated, 'none');

    return new WP_REST_Response($post);
  }

  /**
   * @Route(path="/admin/posts", method="POST")
   */
  public function create(Request $request)
  {
    $post = $request->except(['terms']);

    $created = wp_insert_post($post);

    if (!$created) {
      throw new ControllerException([
        [
          'message' => 'Failed to create post'
        ]
      ], 500);
    }

    $post = get_post($created)->to_array();

    if ($request->has('terms')) {
      $post['terms'] = $this->setTerms($created, $request->param('terms'));
    }

    $post['edit_link'] = get_edit_post_link($post['ID'], 'none');

    return new WP_REST_Response($post);
  }

  /**
   * @Route(path="/admin/posts/count", method="POST")
   */
  public function count(Request $request)
  {
    $post_type = $request->json('post_type', 'post');
    $count = Post::query()
      ->where('post_type', $post_type)
      ->where('post_status', 'publish')
      ->count();

    $data = [
      'count' => $count
    ];

    return new WP_REST_Response($data, 200);
  }

  /**
   * @Route(path="/admin/posts/list", method="POST")
   */
  public function list(Request $request)
  {
    $post_type = $request->json('post_type', 'post');
    $page = $request->json('page', 1);
    $limit = $request->json('limit', 10);
    $fields = $request->json('fields');
    $offset = ($page - 1) * $limit;

    $query = Post::query();

    if ($fields) {
      $query->select($fields);
    }

    $query->where('post_type', $post_type)
      ->where('post_status', 'publish')
      ->limit($limit)
      ->offset($offset);

    $posts = $query->get();
    return new WP_REST_Response($posts, 200);
  }


  private function setTerms($post, $terms)
  {
    $taxonomies = array_unique(array_column($terms, 'taxonomy'));
    foreach ($taxonomies as $taxonomy) {
      wp_set_object_terms($post, [], $taxonomy);
    }

    foreach ($terms as $term) {
      wp_set_post_terms($post, $term['name'], $term['taxonomy'], true);
    }

    return wp_get_post_terms($post, $taxonomies, ['fields' => 'all']);
  }
}
