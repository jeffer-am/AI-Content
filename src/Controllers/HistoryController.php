<?php

namespace AIMuse\Controllers;

use AIMuseVendor\Carbon\Carbon;
use AIMuse\Models\History;
use AIMuse\Attributes\Route;
use AIMuse\Exceptions\ControllerException;
use AIMuse\Helpers\PremiumHelper;
use AIMuse\Middleware\AdminAuth;
use AIMuse\Validators\Validator;
use AIMuse\Helpers\ResponseHelper;
use AIMuseVendor\Illuminate\Support\Facades\DB;
use AIMuse\Validators\HistoryValidator;
use AIMuse\Validators\HistoryTableValidator;

class HistoryController extends Controller
{
  public array $middlewares = [
    AdminAuth::class
  ];

  /**
   * @Route(path="/admin/history", method="POST")
   */
  public function list(Request $request)
  {
    $violations = $request->validate(HistoryValidator::class);

    if ($violations->count() > 0) {
      throw new ControllerException(Validator::toArray($violations), 400);
    }

    $query = History::query()
      ->select([
        DB::raw('SUM(tokens) as tokens'),
        DB::raw('SUM(price) as price'),
      ])->orderBy('created_at', 'ASC');

    $timezone = $request->header('X-Timezone') ?? wp_timezone_string();

    if ($request->groups) {
      foreach ($request->groups as $group) {
        if ($group == 'date') {
          $formats = [
            'hourly' => '%Y-%m-%d %H:00',
            'daily' => '%Y-%m-%d',
            'monthly' => '%Y-%m-01',
          ];

          $format = $formats[$request->format] ?? $formats['daily'];

          $query->addSelect(DB::raw("DATE_FORMAT(CONVERT_TZ(created_at, '+00:00', ?), '$format') as date"))->addBinding($timezone, 'select');
          $query->groupBy('date');
        } else {
          $query->addSelect($group);
          $query->groupBy($group);
        }

        if ($group == 'user_id') {
          $query->with('user');
        }
      }
    }

    if ($request->filters) {
      foreach ($request->filters as $key => $value) {
        $query->where($key, $value);
      }
    }

    $query->whereBetween('created_at', [
      Carbon::parse($request->date['from'])->setTimezone($timezone)->startOfDay()->setTimezone('UTC'),
      Carbon::parse($request->date['to'])->setTimezone($timezone)->endOfDay()->setTimezone('UTC')
    ]);

    return $query->get();
  }

  /**
   * @Route(path="/admin/history/table", method="POST")
   */
  public function table(Request $request)
  {
    $violations = $request->validate(HistoryTableValidator::class);

    if ($violations->count() > 0) {
      throw new ControllerException(Validator::toArray($violations), 400);
    }

    $query = History::query();
    $timezone = $request->header('X-Timezone') ?? wp_timezone_string();

    if ($request->filters) {
      foreach ($request->filters as $key => $value) {
        if ($key == 'date') {
          $query->whereBetween('created_at', [
            Carbon::parse($value['from'])->setTimezone($timezone)->startOfDay()->setTimezone('UTC'),
            Carbon::parse($value['to'])->setTimezone($timezone)->endOfDay()->setTimezone('UTC')
          ]);
        } else {
          $query->where($key, $value);
        }
      }
    }

    $count = $query->count();

    $limit = $request->limit ?? 10;
    $page = $request->page ?? 1;
    $offset = ($page - 1) * $limit;

    if (!PremiumHelper::isPremium()) {
      $page = 1;
      $offset = 0;
    }

    $query->limit($limit)->offset($offset);

    $sort = $request->sort ?? [
      'field' => 'created_at',
      'order' => 'desc',
    ];

    if (!PremiumHelper::isPremium()) {
      $sort = [
        'field' => 'created_at',
        'order' => 'desc',
      ];
    }

    $query->orderBy($sort['field'], $sort['order']);
    $query->with('user');

    return [
      'data' => $query->get(),
      'count' => $count,
      'page' => $page,
    ];
  }

  /**
   * @Route(path="/admin/history/users", method="GET")
   */
  public function users()
  {
    $query = History::query()->addSelect('user_id')->groupBy('user_id')->with('user');

    return $query->get();
  }

}
