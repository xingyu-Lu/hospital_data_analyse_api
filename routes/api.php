<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });


Route::namespace('Api\Back')->prefix('back')->group(function () {
    // 获取 token
    Route::post('authorizations', 'AuthorizationsController@store')->name('login');

    // 需要 token 验证的接口
    Route::middleware(['auth:api', 'permission'])->name('api.back.')->group(function () {
        // 角色管理
        Route::apiResource('roles', 'RolesController');

        //save路由api新增权限
        Route::post('permissions/saveApiPermission', 'PermissionsController@saveApiPermission')->name('permissions.saveApiPermission');
        Route::apiResource('permissions', 'PermissionsController');

        // 登录用户信息
        Route::get('admins/info', 'AdminsController@info')->name('admins.info');

        // 管理员
        Route::put('admins/changepwd', 'AdminsController@changepwd')->name('admins.changepwd');
        Route::put('admins/status', 'AdminsController@status')->name('admins.status');
        Route::apiResource('admins', 'AdminsController');

        // 菜单
        Route::put('menus/status', 'MenusController@status')->name('menus.status');
        Route::get('menus/list', 'MenusController@list')->name('menus.list');
        Route::apiResource('menus', 'MenusController');

        // 更新日志
        Route::get('updatelogs/index', 'UpdateLogsController@index')->name('updatelogs.index');

        // 开单收入
        Route::get('billingincomes/export', 'BillingIncomesController@export')->name('billingincomes/export');
        Route::apiResource('billingincomes', 'BillingIncomesController');

        // 接单收入
        Route::get('receiveincomes/export', 'ReceiveIncomesController@export')->name('receiveincomes/export');
        Route::apiResource('receiveincomes', 'ReceiveIncomesController');

        // 科室列表
        Route::apiResource('offices', 'OfficesController');

        // 支出费用
        Route::get('pays/export', 'PaysController@export')->name('pays/export');
        Route::apiResource('pays', 'PaysController');

        // 重点指标
        Route::get('indicators/export', 'IndicatorsController@export')->name('indicators/export');
        Route::apiResource('indicators', 'IndicatorsController');

        // 成本控制
        Route::get('costControls/export', 'CostControlsController@export')->name('costControls/export');
        Route::apiResource('costControls', 'CostControlsController');

        // 开单排名
        Route::get('billingRanks/export', 'BillingRanksController@export')->name('billingRanks/export');
        Route::apiResource('billingRanks', 'BillingRanksController');

        // 接单排名
        Route::get('receiveRanks/export', 'ReceiveRanksController@export')->name('receiveRanks/export');
        Route::apiResource('receiveRanks', 'ReceiveRanksController');
    });
});