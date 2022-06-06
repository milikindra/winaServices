<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->post('login', 'Login\LoginController@login');
// $router->post('forgetPassword', 'Login\LoginController@forgetPassword');
// $router->post('newPassword', 'Login\LoginController@newPassword');
// $router->post('newPasswordSave', 'Login\LoginController@newPasswordSave');
$router->group(['namespace' => 'Master'], function () use ($router) {
    //employee
    $router->post('employee/getMenu', ['uses' => 'EmployeeController@getMenuList', 'as' => 'employee/getMenu']);
    $router->post('employee/getList', ['uses' =>  'EmployeeController@getList', 'as' => 'employee/getList']);
    $router->post('employeeAddSave', ['uses' => 'EmployeeController@employeeAddSave', 'as' => 'employeeAddSave']);
    $router->post('getEmployeeById', ['uses' => 'EmployeeController@getEmployeeById', 'as' => 'getEmployeeById']);

    // inventory
    $router->post('inventory/getList', ['uses' =>  'InventoryController@getList', 'as' => 'inventory/getList']);
    $router->post('inventoryAddSave', ['uses' => 'InventoryController@inventoryAddSave', 'as' => 'inventoryAddSave']);
    $router->post('inventoryDelete', ['uses' => 'InventoryController@inventoryDelete', 'as' => 'inventoryDelete']);
    $router->post('inventoryEdit', ['uses' => 'InventoryController@inventoryEdit', 'as' => 'inventoryEdit']);
    $router->post('inventoryUpdate', ['uses' => 'InventoryController@inventoryUpdate', 'as' => 'inventoryUpdate']);
    $router->post('kartuStok/getList', ['uses' => 'InventoryController@kartuStokGetList', 'as' => 'kartuStok/getList']);
    $router->post('inventoryGetRawData', ['uses' => 'InventoryController@inventoryGetRawData', 'as' => 'inventoryGetRawData']);

    // province
    $router->post('getProvinceById', ['uses' => 'ProvinceController@getProvinceById', 'as' => 'getProvinceById']);

    // kategori
    $router->post('kategoriGetRawData', ['uses' => 'KategoriController@kategoriGetRawData', 'as' => 'kategoriGetRawData']);

    // subkategori
    $router->post('subKategoriGetRawData', ['uses' => 'SubKategoriController@subKategoriGetRawData', 'as' => 'subKategoriGetRawData']);

    // merk
    $router->post('merkGetRawData', ['uses' => 'MerkController@merkGetRawData', 'as' => 'merkGetRawData']);

    // lokasi
    $router->post('lokasiGetRawData', ['uses' => 'LokasiController@lokasiGetRawData', 'as' => 'lokasiGetRawData']);

    // account
    $router->post('accountGetRawData', ['uses' => 'AccountController@accountGetRawData', 'as' => 'accountGetRawData']);

    // customer
    $router->post('customerGetRawData', ['uses' => 'CustomerController@customerGetRawData', 'as' => 'customerGetRawData']);
    $router->post('customerGetById', ['uses' => 'CustomerController@customerGetById', 'as' => 'customerGetById']);

    // sales
    $router->post('salesGetRawData', ['uses' => 'SalesController@salesGetRawData', 'as' => 'salesGetRawData']);

    // bussiness unit
    $router->post('bussinessUnitGetRawData', ['uses' => 'BussinessUnitController@bussinessUnitGetRawData', 'as' => 'bussinessUnitGetRawData']);

    // department
    $router->post('deptGetRawData', ['uses' => 'DepartmentController@deptGetRawData', 'as' => 'deptGetRawData']);

    // vat
    $router->post('vatGetRawData', ['uses' => 'VatController@vatGetRawData', 'as' => 'vatGetRawData']);
});

$router->group(['namespace' => 'Transaction'], function () use ($router) {
    //SalesOrder
    $router->post('salesOrder/getList', ['uses' =>  'SalesOrderController@getList', 'as' => 'salesOrder/getList']);
    $router->post('salesOrderAddSave', ['uses' => 'SalesOrderController@salesOrderAddSave', 'as' => 'salesOrderAddSave']);
    $router->post('salesOrderDetail', ['uses' => 'SalesOrderController@salesOrderDetail', 'as' => 'salesOrderDetail']);
    $router->post('soGetLastDetail', ['uses' => 'SalesOrderController@soGetLastDetail', 'as' => 'soGetLastDetail']);
});

$router->group(['namespace' => 'Report'], function () use ($router) {
    $router->post('reportPosisiStock', ['uses' =>  'ReportStockController@reportPosisiStock', 'as' => 'reportPosisiStock']);
    //ReportStock
});
