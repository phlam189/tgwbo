<?php

use App\Http\Controllers\AccountBalanceHistoryController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\ContractorController;
use App\Http\Controllers\IntroducerInformationController;
use App\Http\Controllers\BankController;
use App\Http\Controllers\ChargeHistoryController;
use App\Http\Controllers\ClientAggregationController;
use App\Http\Controllers\ClientContractDetailController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ExpenseInformationController;
use App\Http\Controllers\IncomeExpenditureController;
use App\Http\Controllers\InvoiceContructorController;
use App\Http\Controllers\LogTaskController;
use App\Http\Controllers\TaskManagementController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

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

Route::post('/get-token', [UserController::class, 'getToken']);
Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::get('user/info', [UserController::class, 'getUserInfo']);
    Route::Resource('clients', ClientController::class);
    Route::Resource('banks', BankController::class);
    Route::Resource('introducer', IntroducerInformationController::class);
    Route::Resource('contractors', ContractorController::class);
    Route::Resource('accounts', AccountController::class);
    Route::Resource('expenses', ExpenseInformationController::class);
    Route::Resource('client_details', ClientContractDetailController::class);
    // ADM04
    Route::get('income', [\App\Http\Controllers\ClientAggregationController::class, 'getInComeAndExpenditure']);
    Route::get('income-expenditure', [ClientAggregationController::class, 'getSummaryIncomeExpenditure']);
    // CLI 01
    Route::get('transactions', [\App\Http\Controllers\ClientAggregationController::class, 'getTransaction']);
    Route::get('get-client-aggregation-today', [\App\Http\Controllers\ClientAggregationController::class, 'getSummaryClientAggregation']);
    // End CLI 01
    Route::get('account-fee', [\App\Http\Controllers\ClientAggregationController::class, 'getAccountUsageFee']);
    Route::Resource('charge_history', ChargeHistoryController::class);
    Route::Resource('task_management', TaskManagementController::class);
    Route::Resource('log_task', LogTaskController::class);
    Route::apiResource('invoices', \App\Http\Controllers\InvoiceManagementController::class);
    Route::get('/create-task', function () {
        return Artisan::call('task:create', []);
        //
    });
    Route::get('/sync-data', function () {
        return Artisan::call('sync:data', []);
        //
    });
    Route::get('get_contractor', [ContractorController::class, 'getContractor']);
    Route::get('get_clients', [ClientController::class, 'getClient']);
    Route::get('search_account_number', [AccountController::class, 'searchAccountNumber']);
    Route::get('get_list_account_balance', [AccountController::class, 'getListAccountBalances']);
    Route::post('check_unique_email', [ContractorController::class, 'checkUniqueEmail']);
    Route::post('register', [UserController::class, 'register']);
    Route::get('list_contractor_id', [ContractorController::class, 'getListId']);
    Route::post('check_unique_email_introducer', [IntroducerInformationController::class, 'checkUniqueEmail']);
    Route::post('check_unique_email_client', [ClientController::class, 'checkUniqueEmail']);
    Route::get('get_client_with_contractor/{id}', [ClientController::class, 'showWithContractor']);
    Route::get('get_introducer_with_contractor/{id}', [IntroducerInformationController::class, 'showWithContractor']);
    Route::get('get_list_bank_name', [BankController::class, 'getListBankName']);
    Route::get('get_list_bank_name', [BankController::class, 'getListBankName']);
    Route::post('account_balance_history', [AccountBalanceHistoryController::class, 'store']);
    Route::put('account_balance_history/{id}', [AccountBalanceHistoryController::class, 'update']);
    Route::get('export-pdf', [\App\Http\Controllers\InvoiceManagementController::class, 'exportPdf']);
    Route::get('get_account_balance_history', [AccountBalanceHistoryController::class, 'getAccountBalances']);
    Route::get('export_pdf_account', [ClientAggregationController::class, 'export_pdf']);
    Route::resource('invoice_contructor', InvoiceContructorController::class);
    Route::get('check_invoice_contractor_number/{idContractor}/{number}', [InvoiceContructorController::class, 'checkInvoiceNumber']);

    Route::get('get_account_number_by_client', [AccountController::class, 'getAccountNumberByClient']);

    Route::get('download', [\App\Http\Controllers\ActionDownloadController::class, 'download']);
    Route::get('list_contractors', [ContractorController::class, 'listContractor']);
    Route::post('check_contract_detail', [ClientContractDetailController::class, 'checkContractDetailExist']);
    Route::post('check_unique_client_id', [ClientController::class, 'checkUniqueClientId']);
    Route::post('check_unique_account_number', [AccountController::class, 'checkUniqueAccountNumber']);
    Route::resource('log_task', LogTaskController::class);
    Route::put('update_task_management', [TaskManagementController::class, 'updateStatus']);
    Route::Resource('income_expenditure', IncomeExpenditureController::class);
    Route::get('export_income', [IncomeExpenditureController::class, 'exportPdf']);
});