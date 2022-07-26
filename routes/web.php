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
    $router->post('employeeGetRawData', ['uses' => 'EmployeeController@employeeGetRawData', 'as' => 'employeeGetRawData']);

    // inventory
    $router->post('inventory/getList', ['uses' =>  'InventoryController@getList', 'as' => 'inventory/getList']);
    $router->post('inventoryAddSave', ['uses' => 'InventoryController@inventoryAddSave', 'as' => 'inventoryAddSave']);
    $router->post('inventoryDelete', ['uses' => 'InventoryController@inventoryDelete', 'as' => 'inventoryDelete']);
    $router->post('inventoryEdit', ['uses' => 'InventoryController@inventoryEdit', 'as' => 'inventoryEdit']);
    $router->post('inventoryUpdate', ['uses' => 'InventoryController@inventoryUpdate', 'as' => 'inventoryUpdate']);
    $router->post('kartuStok/getList', ['uses' => 'InventoryController@kartuStokGetList', 'as' => 'kartuStok/getList']);
    $router->post('inventoryGetRawData', ['uses' => 'InventoryController@inventoryGetRawData', 'as' => 'inventoryGetRawData']);
    $router->post('inventoryChildGetByHead', ['uses' => 'InventoryController@inventoryChildGetByHead', 'as' => 'inventoryChildGetByHead']);

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
    $router->post('accountGl/getListAccountHistory', ['uses' =>  'AccountController@getListAccountHistory', 'as' => 'accountController/getListAccountHistory']);
    $router->post('accountGl/getListAccount', ['uses' =>  'AccountController@getListAccount', 'as' => 'accountController/getListAccount']);
    $router->post('accountGl/getListCoaTransaction', ['uses' =>  'AccountController@getListCoaTransaction', 'as' => 'accountController/getListCoaTransaction']);
    $router->post('accountGl/getListGlGroupTransaction', ['uses' =>  'AccountController@getListGlGroupTransaction', 'as' => 'accountController/getListGlGroupTransaction']);
    $router->post('accountGl/getListCashBankDetail', ['uses' =>  'AccountController@getListCashBankDetail', 'as' => 'accountController/getListCashBankDetail']);
    $router->post('trxTypeFromGlCard', ['uses' => 'AccountController@trxTypeFromGlCard', 'as' => 'trxTypeFromGlCard']);

    // customer
    $router->post('customerGetRawData', ['uses' => 'CustomerController@customerGetRawData', 'as' => 'customerGetRawData']);
    $router->post('customerGetById', ['uses' => 'CustomerController@customerGetById', 'as' => 'customerGetById']);
    $router->post('customerGetForSi', ['uses' => 'CustomerController@customerGetForSi', 'as' => 'customerGetForSi']);
    $router->post('customer/getList', ['uses' => 'CustomerController@getList', 'as' => 'customer/getList']);
    $router->post('customerAddSave', ['uses' => 'CustomerController@customerAddSave', 'as' => 'customerAddSave']);
    $router->post('customerEdit', ['uses' => 'CustomerController@customerEdit', 'as' => 'customerEdit']);
    $router->post('customerUpdate', ['uses' => 'CustomerController@customerUpdate', 'as' => 'customerUpdate']);


    // sales
    $router->post('salesGetRawData', ['uses' => 'SalesController@salesGetRawData', 'as' => 'salesGetRawData']);
    $router->post('sales/getList', ['uses' => 'SalesController@getList', 'as' => 'sales/getList']);

    // suuplier
    $router->post('supplier/getList', ['uses' => 'SupplierController@getList', 'as' => 'supplier/getList']);

    // bussiness unit
    $router->post('bussinessUnitGetRawData', ['uses' => 'BussinessUnitController@bussinessUnitGetRawData', 'as' => 'bussinessUnitGetRawData']);

    // department
    $router->post('deptGetRawData', ['uses' => 'DepartmentController@deptGetRawData', 'as' => 'deptGetRawData']);

    // vat
    $router->post('vatGetRawData', ['uses' => 'VatController@vatGetRawData', 'as' => 'vatGetRawData']);

    // globalParam
    $router->post('getGlobalParam', ['uses' => 'GlobalParamController@getGlobalParam', 'as' => 'getGlobalParam']);

    // vintras
    $router->post('vintrasGetData', ['uses' => 'VintrasController@vintrasGetData', 'as' => 'vintrasGetData']);
    $router->post('vintras/getList', ['uses' => 'VintrasController@getList', 'as' => 'vintras/getList']);

    // area
    $router->post('areaGetRawData', ['uses' => 'AreaController@areaGetRawData', 'as' => 'areaGetRawData']);
});

$router->group(['namespace' => 'Transaction'], function () use ($router) {
    //SalesOrder
    $router->post('salesOrder/getList', ['uses' =>  'SalesOrderController@getList', 'as' => 'salesOrder/getList']);
    $router->post('salesOrder/getlistHead', ['uses' =>  'SalesOrderController@getlistHead', 'as' => 'salesOrder/getlistHead']);
    $router->post('salesOrderAddSave', ['uses' => 'SalesOrderController@salesOrderAddSave', 'as' => 'salesOrderAddSave']);
    $router->post('salesOrderDetail', ['uses' => 'SalesOrderController@salesOrderDetail', 'as' => 'salesOrderDetail']);
    $router->post('soGetLastDetail', ['uses' => 'SalesOrderController@soGetLastDetail', 'as' => 'soGetLastDetail']);
    $router->post('soGetById', ['uses' => 'SalesOrderController@soGetById', 'as' => 'soGetById']);
    $router->post('salesOrderUpdate', ['uses' => 'SalesOrderController@salesOrderUpdate', 'as' => 'salesOrderUpdate']);
    $router->post('salesOrderStatus', ['uses' =>  'SalesOrderController@salesOrderStatus', 'as' => 'salesOrderStatus']);
    $router->post('salesOrderDelete', ['uses' =>  'SalesOrderController@salesOrderDelete', 'as' => 'salesOrderDelete']);
    $router->post('salesOrderUpdateState', ['uses' => 'SalesOrderController@salesOrderUpdateState', 'as' => 'salesOrderUpdateState']);


    // SalesInvoice
    $router->post('siGetEfaktur', ['uses' => 'SalesInvoiceController@siGetEfaktur', 'as' => 'siGetEfaktur']);
});

$router->group(['namespace' => 'Report'], function () use ($router) {
    //ReportStock
    $router->post('reportPosisiStock', ['uses' =>  'ReportStockController@reportPosisiStock', 'as' => 'reportPosisiStock']);
});

$router->group(['namespace' => 'Finance'], function () use ($router) {
    // generalLedger
    // $router->post('generalLedger/getListAccount', ['uses' =>  'GeneralLedgerController@getListAccount', 'as' => 'generalLedger/getListAccount']);

    // financialReport
    $router->post('financialReport/getListIncomeStatement', ['uses' =>  'FinancialReportController@getListIncomeStatement', 'as' => 'financialReport/getListIncomeStatement']);
    $router->post('financialReport/getListBalanceSheet', ['uses' =>  'FinancialReportController@getListBalanceSheet', 'as' => 'financialReport/getListBalanceSheet']);
    $router->post('financialReport/getListPnlProject', ['uses' =>  'FinancialReportController@getListPnlProject', 'as' => 'financialReport/getListPnlProject']);
    $router->post('financialReport/getListPnlProjectList', ['uses' =>  'FinancialReportController@getListPnlProjectList', 'as' => 'financialReport/getListPnlProjectList']);
    $router->post('getPnlProject', ['uses' =>  'FinancialReportController@getPnlProject', 'as' => 'getPnlProject']);
    $router->post('pnlProjectSave', ['uses' =>  'FinancialReportController@pnlProjectSave', 'as' => 'pnlProjectSave']);

    // statementOfAccount
    $router->post('statementOfAccount/getListCustomerSOA', ['uses' =>  'StatementOfAccountController@getListCustomerSOA', 'as' => 'statementOfAccount/getListCustomerSOA']);
    $router->post('statementOfAccount/getListSupplierSOA', ['uses' =>  'StatementOfAccountController@getListSupplierSOA', 'as' => 'statementOfAccount/getListSupplierSOA']);
    $router->post('statementOfAccount/updateIn', ['uses' =>  'StatementOfAccountController@updateIn', 'as' => 'statementOfAccount/updateIn']);
});
