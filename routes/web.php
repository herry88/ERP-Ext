<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

//Route::get('/', function () {
//    return view('welcome');
//});

//Route::get('/', 'SiteController@index')->name('site');

Route::get('/sale_order', 'SaleOrderController@index')->name('sale_order');

Route::get('/site', 'SiteController@index')->name('site');
Route::get('/site/dashboard', 'SiteController@dashboard')->name('site.dashboard');
Route::get('/site/data', 'SiteController@index_data')->name('site.data');
Route::get('/site/detail/{id}','SiteController@show');
Route::get('/site/export/{id}', 'SiteController@export')->name('site.export');

Route::get('/dashboard', 'DashboardController@index')->name('dashboard');
Route::get('/dashboard/data', 'DashboardController@data')->name('dashboard.data');

Route::get('/project/paket_project', 'ProjectController@project_batch')->name('project.project_batch');
Route::get('/project/paket_project_detail/{packet_id}/{customer_id}', 'ProjectController@project_batch_detail')->name('project.project_batch_detail');

Route::get('/', 'BudgetController@report')->name('site.report.budget');
Route::get('/financial_report/budget', 'BudgetController@report')->name('site.report.budget');
Route::get('/financial_report/budget/data', 'BudgetController@reportdata')->name('site.report.budget.data');
Route::get('/financial_report/budget/detail/{id}','BudgetController@reportdetail');
Route::get('/financial_report/budget/detail_analyze/{id}','BudgetController@detail_analyze')->name('site.report.analyze');

Route::get('/finance/report_project', 'FinanceController@reportproject')->name('finance.report_project');
Route::post('/finance/report_project', 'FinanceController@reportproject')->name('finance.report_project');
Route::get('/finance/report_project_detail/{customer_id}/{year}/{site_type_id}/{date_filter}/{check_ignore_filter}', 'FinanceController@reportprojectdetail')->name('finance.report_project.detail');
Route::get('/finance/report_project_detail/export/{customer_id}/{year}/{site_type_id}/{date_filter}/{check_ignore_filter}', 'FinanceController@reportprojectdetailexport')->name('finance.report_project.detail.export');
Route::get('/finance/report_budget_dept', 'FinanceController@reportBudgetDept')->name('finance.report_budget_dept');
Route::get('/finance/report_budget_dept_detail/{tahun}', 'FinanceController@reportBudgetDeptDetail')->name('finance.report_budget_dept_detail');
Route::get('/finance/test', 'FinanceController@SampleTest');

Route::get('/marketing', 'MarketingController@index')->name('marketing.index');

Route::get('sales_order/detail/{tahun}','SalesOrderController@show');

//Monitoring Preventive
Route::get('/finance/monitoring_preventive', 'financeController@monitoring_preventive')->name('finance.monitoring_preventive');
Route::get('/finance/monitoring_preventive_detail','financeController@monitoring_preventive_detail')->name('finance.monitoring_preventive_detail');


