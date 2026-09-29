<?php

namespace App\Http\Controllers\Admin\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Hỗ trợ các trang list trong admin phản hồi JSON cho bảng AJAX
 * (component Alpine x-admin.data-table).
 *
 * Contract:
 *   GET ...?ajax=1&q=&sort=&dir=&page=&per_page=
 *   → { recordsTotal, recordsFiltered, page, per_page, html }
 */
trait RendersAdminTables
{
    protected function wantsTableAjax(Request $request): bool
    {
        return $request->ajax() || $request->boolean('ajax');
    }

    /**
     * @param  \Closure(): Builder  $baseQuery   builder KHÔNG filter (dùng để đếm recordsTotal)
     * @param  \Closure(Builder, Request): void  $applyFilters  áp filter từ request vào builder
     * @param  array<string, string>  $sortable   alias cột => cột DB (whitelist sort)
     * @param  array<string, string>  $defaultOrder  thứ tự mặc định khi không sort (column => dir)
     */
    protected function tableJson(
        Request $request,
        \Closure $baseQuery,
        \Closure $applyFilters,
        string $rowsView,
        string $itemsVar = 'products',
        array $viewData = [],
        array $sortable = [],
        array $defaultOrder = ['id' => 'asc'],
        int $defaultPerPage = 15,
    ): JsonResponse {
        $perPage = max(1, min((int) $request->input('per_page', $defaultPerPage), 100));
        $page = max(1, (int) $request->input('page', 1));

        $recordsTotal = ($baseQuery)()->count();

        $filtered = ($baseQuery)();
        ($applyFilters)($filtered, $request);
        $recordsFiltered = (clone $filtered)->count();

        $lastPage = max(1, (int) ceil($recordsFiltered / $perPage));
        $page = min($page, $lastPage);

        $dir = strtolower((string) $request->input('dir', ''));
        $dir = in_array($dir, ['asc', 'desc'], true) ? $dir : null;

        $sort = $request->input('sort');
        if (is_string($sort) && $dir !== null && isset($sortable[$sort])) {
            $filtered->orderBy($sortable[$sort], $dir)->orderBy('id', $dir);
        } else {
            foreach ($defaultOrder as $column => $direction) {
                $filtered->orderBy($column, $direction);
            }
        }

        $items = $filtered->forPage($page, $perPage)->get();

        return response()->json([
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'page' => $page,
            'per_page' => $perPage,
            'html' => view($rowsView, $viewData + [$itemsVar => $items])->render(),
        ]);
    }
}
