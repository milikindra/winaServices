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

    // province
    $router->post('getProvinceById', ['uses' => 'ProvinceController@getProvinceById', 'as' => 'getProvinceById']);

    // kategori
    $router->post('kategoriGetRawData', ['uses' => 'KategoriController@kategoriGetRawData', 'as' => 'kategoriGetRawData']);

    // subkategori
    $router->post('subKategoriGetRawData', ['uses' => 'SubKategoriController@subKategoriGetRawData', 'as' => 'subKategoriGetRawData']);
});
// $router->group(['middleware' => ['auth']], function () use ($router) {
//     $router->group(['namespace' => 'Master'], function () use ($router) {
//         $router->post('employee/getMenu', ['uses' => 'EmployeeController@getMenuList', 'as' => 'employee/getMenu']);
//         $router->post('employee/getList', ['uses' =>  'EmployeeController@getList', 'as' => 'employee/getList']);
//     });
// });
