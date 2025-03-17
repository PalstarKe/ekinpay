<?php

use App\Http\Controllers\AiTemplateController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\BankAccountController;
use App\Http\Controllers\BankTransferController;
use App\Http\Controllers\BankTransferPaymentController;
use App\Http\Controllers\RouterController;
use App\Http\Controllers\RadiusUserController;
use App\Http\Controllers\SmsController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\ChartOfAccountController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\NasController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomFieldController;
use App\Http\Controllers\CustomQuestionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\DucumentUploadController;
use App\Http\Controllers\EmailTemplateController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\FlutterwavePaymentController;
use App\Http\Controllers\FormBuilderController;
use App\Http\Controllers\GoalController;
use App\Http\Controllers\GoalTrackingController;
use App\Http\Controllers\GoalTypeController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\LabelController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\LeadStageController;
use App\Http\Controllers\NotificationTemplatesController;
use App\Http\Controllers\OtherPaymentController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PaypalController;
use App\Http\Controllers\PaystackPaymentController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PipelineController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\PlanRequestController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RevenueController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SkrillPaymentController;
use App\Http\Controllers\SourceController;
use App\Http\Controllers\StageController;
use App\Http\Controllers\StripePaymentController;
use App\Http\Controllers\SupportController;
use App\Http\Controllers\SystemController;
use App\Http\Controllers\TaskStageController;
use App\Http\Controllers\TaxController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\TransferController;
use App\Http\Controllers\TravelController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ZoomMeetingController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\FupController;
use App\Http\Controllers\Tr069Controller;
use App\Http\Controllers\ReferralProgramController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\CaptivePortalController;


require __DIR__ . '/auth.php';

// Route::get('/captive/{nas_ip?}', [CaptivePortalController::class, 'showLogin']);
// Route::get('/captive/{nas_ip?}', [CaptivePortalController::class, 'showLogin'])->name('captive.showLogin');
Route::domain('captive.thefuturefirm.net')->group(function () {
    Route::get('/hs/{nas_ip?}', [CaptivePortalController::class, 'showLogin'])->name('captive.showLogin');
    Route::post('/hs/process-customer', [CaptivePortalController::class, 'processCustomer'])->name('processCustomer');
    Route::post('/hs/query-mpesa', [CaptivePortalController::class, 'processQueryMpesa'])->name('processQueryMpesa');
    // Mpesa callback route
});
Route::post('/hs/mpesa-callback', [CaptivePortalController::class, 'mpesaCallback'])->name('mpesaCallback');
//================================= Invoice Payment Gateways  ====================================//

Route::post('/customer-pay-with-bank', [BankTransferPaymentController::class, 'customerPayWithBank'])->name('customer.pay.with.bank');
Route::get('invoice/{id}/action', [BankTransferPaymentController::class, 'invoiceAction'])->name('invoice.action');
Route::post('invoice/{id}/changeaction', [BankTransferPaymentController::class, 'invoiceChangeStatus'])->name('invoice.changestatus');

Route::post('{id}/pay-with-paypal', [PaypalController::class, 'customerPayWithPaypal'])->name('customer.pay.with.paypal');
Route::get('{id}/get-payment-status/{amount}', [PaypalController::class, 'customerGetPaymentStatus'])->name('customer.get.payment.status');

Route::post('/customer-pay-with-paystack', [PaystackPaymentController::class, 'customerPayWithPaystack'])->name('customer.pay.with.paystack');
Route::get('/customer/paystack/{pay_id}/{invoice_id}', [PaystackPaymentController::class, 'getInvoicePaymentStatus'])->name('customer.paystack');

Route::post('/customer-pay-with-flaterwave', [FlutterwavePaymentController::class, 'customerPayWithFlutterwave'])->name('customer.pay.with.flaterwave');
Route::get('/customer/flaterwave/{txref}/{invoice_id}', [FlutterwavePaymentController::class, 'getInvoicePaymentStatus'])->name('customer.flaterwave');

/***********************************************************************************************************************************************/

// Invoice Payment Gateways
Route::post('customer/{id}/payment', [StripePaymentController::class, 'addpayment'])->name('customer.payment');
Route::get('invoice/pdf/{id}', [InvoiceController::class, 'invoice'])->name('invoice.pdf');

Route::get('users/{id}/login-with-company', [UserController::class, 'LoginWithCompany'])->name('login.with.company')->middleware(['auth']);
Route::get('login-with-company/exit', [UserController::class, 'ExitCompany'])->name('exit.company')->middleware(['auth']);
Route::get('user-login/{id}', [UserController::class, 'LoginManage'])->name('users.login');

Route::get('/form/{code}', [FormBuilderController::class, 'formView'])->name('form.view');
Route::post('/form_view_store', [FormBuilderController::class, 'formViewStore'])->name('form.view.store');

Route::get('/', [DashboardController::class, 'landingpage']);


// cache
Route::get('/config-cache', function () {
    Artisan::call('cache:clear');
    Artisan::call('route:clear');
    Artisan::call('view:clear');
    Artisan::call('optimize:clear');
    return redirect()->back()->with('success', 'Cache Clear Successfully');
})->name('config.cache');


//================================= Invoice Payment Gateways  ====================================//
Route::group(['middleware' => ['verified']], function () {

    // Route::get('/home', [DashboardController::class, 'dashboard_index'])->name('home');

    Route::get('/home', [DashboardController::class, 'show_dashboard'])->name('home')->middleware(['auth']);
    // Route::get('/account-dashboard', [DashboardController::class, 'account_dashboard_index'])->name('dashboard')->middleware(['auth']);
    Route::get('home/counts', [DashboardController::class, 'getNasCounts'])->name('nas.counts');

    Route::get('profile', [UserController::class, 'profile'])->name('profile')->middleware(['auth']);

    Route::any('edit-profile', [UserController::class, 'editprofile'])->name('update.account')->middleware(['auth']);

    Route::resource('users', UserController::class)->middleware(['auth']);

    Route::post('change-password', [UserController::class, 'updatePassword'])->name('update.password');

    Route::any('user-reset-password/{id}', [UserController::class, 'userPassword'])->name('users.reset');

    Route::post('user-reset-password/{id}', [UserController::class, 'userPasswordReset'])->name('user.password.update');
    Route::get('company-info/{id}', [UserController::class, 'companyInfo'])->name('company.info');
    Route::post('user-unable', [UserController::class, 'userUnable'])->name('user.unable');

    Route::get('/change/mode', [UserController::class, 'changeMode'])->name('change.mode');

    Route::resource('roles', RoleController::class)->middleware(['auth']);

    Route::resource('permissions', PermissionController::class)->middleware(['auth']);

    Route::group(
        [
            'middleware' => [
                'auth'
            ],
        ],
        function () {
            Route::get('change-language/{lang}', [LanguageController::class, 'changeLanquage'])->name('change.language');

            Route::get('manage-language/{lang}', [LanguageController::class, 'manageLanguage'])->name('manage.language');

            Route::post('store-language-data/{lang}', [LanguageController::class, 'storeLanguageData'])->name('store.language.data');

            Route::get('create-language', [LanguageController::class, 'createLanguage'])->name('create.language');

            Route::any('store-language', [LanguageController::class, 'storeLanguage'])->name('store.language');

            Route::delete('/lang/{lang}', [LanguageController::class, 'destroyLang'])->name('lang.destroy');
        }
    );

    Route::group(
        [
            'middleware' => [
                'auth'
            ],
        ],
        function () {
            Route::resource('systems', SystemController::class);
            Route::post('email-settings', [SystemController::class, 'saveEmailSettings'])->name('email.settings');
            Route::post('company-email-settings', [SystemController::class, 'saveCompanyEmailSettings'])->name('company.email.settings');

            Route::post('company-settings', [SystemController::class, 'saveCompanySettings'])->name('company.settings');
            Route::post('system-settings', [SystemController::class, 'saveSystemSettings'])->name('system.settings');
            Route::post('tracker-settings', [SystemController::class, 'saveTrackerSettings'])->name('tracker.settings');
            Route::post('slack-settings', [SystemController::class, 'saveSlackSettings'])->name('slack.settings');
            Route::post('telegram-settings', [SystemController::class, 'saveTelegramSettings'])->name('telegram.settings');
            Route::post('sms-settings', [SystemController::class, 'saveSMSSettings'])->name('sms.setting');
            Route::post('whatsapp-settings', [SystemController::class, 'saveWhatsappSettings'])->name('whatsapp.setting');
            Route::get('print-setting', [SystemController::class, 'printIndex'])->name('print.setting');
            Route::get('settings', [SystemController::class, 'companyIndex'])->name('settings');
            Route::post('business-setting', [SystemController::class, 'saveBusinessSettings'])->name('business.setting');
            Route::post('company-payment-setting', [SystemController::class, 'saveCompanyPaymentSettings'])->name('company.payment.settings');
            Route::post('currency-settings', [SystemController::class, 'saveCurrencySettings'])->name('currency.settings');
            Route::post('company-preview', [SystemController::class, 'currencyPreview'])->name('currency.preview');


            Route::any('test-mail', [SystemController::class, 'testMail'])->name('test.mail');
            Route::post('test-mail/send', [SystemController::class, 'testSendMail'])->name('test.send.mail');

            Route::post('stripe-settings', [SystemController::class, 'savePaymentSettings'])->name('payment.settings');
            Route::post('pusher-setting', [SystemController::class, 'savePusherSettings'])->name('pusher.setting');
            Route::post('recaptcha-settings', [SystemController::class, 'recaptchaSettingStore'])->name('recaptcha.settings.store')->middleware(['auth']);

            Route::post('seo-settings', [SystemController::class, 'seoSettings'])->name('seo.settings.store')->middleware(['auth']);
            Route::any('webhook-settings', [SystemController::class, 'webhook'])->name('webhook.settings')->middleware(['auth']);
            Route::get('webhook-settings/create', [SystemController::class, 'webhookCreate'])->name('webhook.create')->middleware(['auth']);
            Route::post('webhook-settings/store', [SystemController::class, 'webhookStore'])->name('webhook.store');
            Route::get('webhook-settings/{wid}/edit', [SystemController::class, 'webhookEdit'])->name('webhook.edit')->middleware(['auth']);
            Route::post('webhook-settings/{wid}/edit', [SystemController::class, 'webhookUpdate'])->name('webhook.update')->middleware(['auth']);
            Route::delete('webhook-settings/{wid}', [SystemController::class, 'webhookDestroy'])->name('webhook.destroy')->middleware(['auth']);

            Route::post('cookie-setting', [SystemController::class, 'saveCookieSettings'])->name('cookie.setting');

            Route::post('cache-settings', [SystemController::class, 'cacheSettingStore'])->name('cache.settings.store')->middleware(['auth']);
        }
    );

    //Customer
    Route::group(
        [
            'middleware' => [
                'auth'
            ],
        ],
        function () {
            Route::resource('customer', CustomerController::class);
            Route::post('customer/{id}/update-expiry', [CustomerController::class, 'updateExpiry'])->name('customer.updateExpiry');
            Route::get('customer/{id}/update-extend', [CustomerController::class, 'updateExtend'])->name('customer.updateExtend');
            Route::post('customer/{id}/update-balance', [CustomerController::class, 'depositCash'])->name('customer.depositCash');
            Route::post('customer/{id}/use-balance', [CustomerController::class, 'useBalance'])->name('customer.useBalance');
            Route::get('customer/{username}/live-usage', [CustomerController::class, 'getLiveUsage']);
            Route::post('customer/{id}/change-plan', [CustomerController::class, 'changePlan'])->name('customer.changePlan');
            Route::post('customer/{id}/deactivate', [CustomerController::class, 'deactivate'])->name('customer.deactivate');
            Route::post('customer/{id}/clearmac', [CustomerController::class, 'clearMac'])->name('customer.clearmac');
            Route::post('customer/{id}/refresh', [CustomerController::class, 'refreshAccount'])->name('customer.refresh');
            Route::post('customer/{id}/corporate', [CustomerController::class, 'asCorporate'])->name('customer.corporate');
        }
    );

    Route::group(
        [
            'middleware' => [
                'auth'
            ],
        ],
        function (){
            Route::get('sms/delivery', [SmsController::class, 'smsDelivery'])->name('sms.delivery');
            Route::get('sms/bulk', [SmsController::class, 'bulkSmsForm'])->name('sms.bulk.form');
            Route::get('sms/sendbulksms', [SmsController::class, 'sendBulkSms'])->name('sms.bulk.send');
            Route::resource('sms', SmsController::class)->parameters([
                'sms' => 'sms'
            ]);
           
        }
    );

    Route::group(
        [
            'middleware' => ['auth'],
        ],
        function () {
            Route::resource('nas', NasController::class)->parameters([
                'nas' => 'nas'
            ]);
            Route::post('nas/{id}/assign-package', [NasController::class, 'assignPackage'])->name('nas.assignPackage')->middleware(['auth']);
            Route::get('nas/status', [NasController::class, 'getNasStatus'])->name('nas.status');
        }
    );
    Route::resource('packages', PackageController::class)->middleware(['auth']);

    Route::resource('tr069', PackageController::class)->middleware(['auth']);
    Route::resource('fup', PackageController::class)->middleware(['auth']);

    Route::prefix('routers')->group(function () {
        Route::get('/', [RouterController::class, 'index']);
        Route::post('/', [RouterController::class, 'store']);
        Route::get('{id}', [RouterController::class, 'show']);
        Route::put('{id}', [RouterController::class, 'update']);
        Route::delete('{id}', [RouterController::class, 'destroy']);
    });
    
    Route::prefix('radius-users')->group(function () {
        Route::get('/', [RadiusUserController::class, 'index']);
        Route::post('/', [RadiusUserController::class, 'store']);
        Route::get('{id}', [RadiusUserController::class, 'show']);
        Route::put('{id}', [RadiusUserController::class, 'update']);
        Route::delete('{id}', [RadiusUserController::class, 'destroy']);
    });
    
    Route::resource('taxes', TaxController::class)->middleware(['auth']);

    Route::group(
        [
            'middleware' => [
                'auth'
            ],
        ],
        function () {
            Route::get('invoice/{id}/duplicate', [InvoiceController::class, 'duplicate'])->name('invoice.duplicate');
            Route::get('invoice/{id}/payment/reminder', [InvoiceController::class, 'paymentReminder'])->name('invoice.payment.reminder');
            Route::post('invoice/customer', [InvoiceController::class, 'customer'])->name('invoice.customer');
            Route::get('invoice/{id}/sent', [InvoiceController::class, 'sent'])->name('invoice.sent');
            Route::get('invoice/{id}/resent', [InvoiceController::class, 'resent'])->name('invoice.resent');
            Route::get('invoice/{id}/payment', [InvoiceController::class, 'payment'])->name('invoice.payments');
            Route::post('invoice/{id}/payment', [InvoiceController::class, 'createPayment'])->name('invoice.payment');
            Route::post('invoice/{id}/payment/{pid}/destroy', [InvoiceController::class, 'paymentDestroy'])->name('invoice.payment.destroy');
            Route::get('invoice/items', [InvoiceController::class, 'items'])->name('invoice.items');
            Route::resource('invoice', InvoiceController::class);
            Route::get('invoice/create/{cid}', [InvoiceController::class, 'create'])->name('invoice.create');
            Route::get('export/invoice', [InvoiceController::class, 'export'])->name('invoice.export');
            Route::get('/customer/invoice/{id}/', [InvoiceController::class, 'invoiceLink'])->name('invoice.link.copy');

        }
    );
   
    Route::get('/invoices/preview/{template}/{color}', [InvoiceController::class, 'previewInvoice'])->name('invoice.preview');
    Route::post('/invoices/template/setting', [InvoiceController::class, 'saveTemplateSettings'])->name('template.setting');


    Route::resource('taxes', TaxController::class)->middleware(['auth']);


    Route::resource('revenue', RevenueController::class)->middleware(['auth']);

    Route::resource('payment', PaymentController::class)->middleware(['auth']);
    Route::resource('fup', FupController::class)->middleware(['auth']);
    Route::resource('tr069', Tr069Controller::class)->middleware(['auth']);

    Route::group(
        [
            'middleware' => [
                'auth'
            ],
        ],
        function () {
            Route::get('report/transaction', [TransactionController::class, 'index'])->name('transaction.index');
        }
    );

    Route::group(
        [
            'middleware' => [
                'auth'
            ],
        ],
        function () {
            Route::get('report/income-summary', [ReportController::class, 'incomeSummary'])->name('report.income.summary');
            Route::get('report/expense-summary', [ReportController::class, 'expenseSummary'])->name('report.expense.summary');
            Route::get('report/income-vs-expense-summary', [ReportController::class, 'incomeVsExpenseSummary'])->name('report.income.vs.expense.summary');
            Route::get('report/tax-summary', [ReportController::class, 'taxSummary'])->name('report.tax.summary');
            //        Route::get('report/profit-loss-summary', [ReportController::class, 'profitLossSummary'])->name('report.profit.loss.summary');
            Route::get('report/invoice-summary', [ReportController::class, 'invoiceSummary'])->name('report.invoice.summary');
            Route::get('report/invoice-report', [ReportController::class, 'invoiceReport'])->name('report.invoice');
            Route::get('report/account-statement-report', [ReportController::class, 'accountStatement'])->name('report.account.statement');
            Route::get('reports-monthly-cashflow', [ReportController::class, 'monthlyCashflow'])->name('report.monthly.cashflow')->middleware(['auth']);
            Route::get('reports-quarterly-cashflow', [ReportController::class, 'quarterlyCashflow'])->name('report.quarterly.cashflow')->middleware(['auth']);
            Route::get('report/sales', [ReportController::class, 'salesReport'])->name('report.sales');
            Route::post('export/sales', [ReportController::class, 'salesReportExport'])->name('sales.export');
            Route::get('report/receivables', [ReportController::class, 'ReceivablesReport'])->name('report.receivables');
            Route::post('export/receivables', [ReportController::class, 'ReceivablesExport'])->name('receivables.export');
            Route::get('report/payables', [ReportController::class, 'PayablesReport'])->name('report.payables');
        }
    );

    Route::resource('goal', GoalController::class)->middleware(['auth']);

    //Budget Planner //
    Route::resource('budget', BudgetController::class)->middleware(['auth']);

    Route::resource('account-assets', AssetController::class)->middleware(['auth']);

    Route::resource('custom-field', CustomFieldController::class)->middleware(['auth']);

    Route::post('chart-of-account/subtype', [ChartOfAccountController::class, 'getSubType'])->name('charofAccount.subType')->middleware(['auth']);

    Route::group(
        [
            'middleware' => [
                'auth'
            ],
        ],
        function () {
            Route::resource('chart-of-account', ChartOfAccountController::class);
        }
    );


    // Client Module

    Route::resource('clients', ClientController::class)->middleware(['auth']);

    Route::any('client-reset-password/{id}', [ClientController::class, 'clientPassword'])->name('clients.reset');
    Route::post('client-reset-password/{id}', [ClientController::class, 'clientPasswordReset'])->name('client.password.update');


    Route::get('/search', [UserController::class, 'search'])->name('search.json');
    Route::post('/stages/order', [StageController::class, 'order'])->name('stages.order');
    Route::post('/stages/json', [StageController::class, 'json'])->name('stages.json');

    Route::resource('stages', StageController::class);
    Route::resource('pipelines', PipelineController::class);
    Route::resource('labels', LabelController::class);
    Route::resource('sources', SourceController::class);
    Route::resource('payments', PaymentController::class);
    Route::resource('custom_fields', CustomFieldController::class);

    // Leads Module

    Route::post('/lead_stages/order', [LeadStageController::class, 'order'])->name('lead_stages.order');

    Route::resource('lead_stages', LeadStageController::class)->middleware(['auth']);

    Route::post('/leads/json', [LeadController::class, 'json'])->name('leads.json');
    Route::post('/leads/order', [LeadController::class, 'order'])->name('leads.order')->middleware(['auth']);
    Route::get('/leads/list', [LeadController::class, 'lead_list'])->name('leads.list')->middleware(['auth']);
    Route::post('/leads/{id}/file', [LeadController::class, 'fileUpload'])->name('leads.file.upload')->middleware(['auth']);
    Route::get('/leads/{id}/file/{fid}', [LeadController::class, 'fileDownload'])->name('leads.file.download')->middleware(['auth']);
    Route::delete('/leads/{id}/file/delete/{fid}', [LeadController::class, 'fileDelete'])->name('leads.file.delete')->middleware(['auth']);
    Route::post('/leads/{id}/note', [LeadController::class, 'noteStore'])->name('leads.note.store')->middleware(['auth']);
    Route::get('/leads/{id}/labels', [LeadController::class, 'labels'])->name('leads.labels')->middleware(['auth']);
    Route::post('/leads/{id}/labels', [LeadController::class, 'labelStore'])->name('leads.labels.store')->middleware(['auth']);
    Route::get('/leads/{id}/users', [LeadController::class, 'userEdit'])->name('leads.users.edit')->middleware(['auth']);
    Route::put('/leads/{id}/users', [LeadController::class, 'userUpdate'])->name('leads.users.update')->middleware(['auth']);
    Route::delete('/leads/{id}/users/{uid}', [LeadController::class, 'userDestroy'])->name('leads.users.destroy')->middleware(['auth']);
    Route::get('/leads/{id}/sources', [LeadController::class, 'sourceEdit'])->name('leads.sources.edit')->middleware(['auth']);
    Route::put('/leads/{id}/sources', [LeadController::class, 'sourceUpdate'])->name('leads.sources.update')->middleware(['auth']);
    Route::delete('/leads/{id}/sources/{uid}', [LeadController::class, 'sourceDestroy'])->name('leads.sources.destroy')->middleware(['auth']);
    Route::get('/leads/{id}/discussions', [LeadController::class, 'discussionCreate'])->name('leads.discussions.create')->middleware(['auth']);
    Route::post('/leads/{id}/discussions', [LeadController::class, 'discussionStore'])->name('leads.discussion.store')->middleware(['auth']);
    Route::get('/leads/{id}/show_convert', [LeadController::class, 'showConvertToDeal'])->name('leads.convert.deal')->middleware(['auth']);
    Route::post('/leads/{id}/convert', [LeadController::class, 'convertToDeal'])->name('leads.convert.to.deal')->middleware(['auth']);
    Route::get('/leads/export', [LeadController::class, 'export'])->name('leads.export')->middleware(['auth']);

    // Route::post('import/leads', [LeadController::class, 'import'])->name('leads.import');
    Route::get('import/leads/file', [LeadController::class, 'importFile'])->name('leads.import');
    Route::post('leads/import', [LeadController::class, 'fileImport'])->name('leads.file.import');
    Route::get('import/leads/modal', [LeadController::class, 'fileImportModal'])->name('leads.import.modal');
    Route::post('import/leads', [LeadController::class, 'leadImportdata'])->name('leads.import.data');

    // Lead Calls
    Route::get('/leads/{id}/call', [LeadController::class, 'callCreate'])->name('leads.calls.create')->middleware(['auth']);
    Route::post('/leads/{id}/call', [LeadController::class, 'callStore'])->name('leads.calls.store')->middleware(['auth']);
    Route::get('/leads/{id}/call/{cid}/edit', [LeadController::class, 'callEdit'])->name('leads.calls.edit')->middleware(['auth']);
    Route::put('/leads/{id}/call/{cid}', [LeadController::class, 'callUpdate'])->name('leads.calls.update')->middleware(['auth']);
    Route::delete('/leads/{id}/call/{cid}', [LeadController::class, 'callDestroy'])->name('leads.calls.destroy')->middleware(['auth']);

    // Lead Email

    Route::get('/leads/{id}/email', [LeadController::class, 'emailCreate'])->name('leads.emails.create')->middleware(['auth']);
    Route::post('/leads/{id}/email', [LeadController::class, 'emailStore'])->name('leads.emails.store')->middleware(['auth']);

    Route::resource('leads', LeadController::class)->middleware(['auth']);

    // end Leads Module

    Route::get('user/{id}/plan', [UserController::class, 'upgradePlan'])->name('plan.upgrade')->middleware(['auth']);
    Route::get('user/{id}/plan/{pid}', [UserController::class, 'activePlan'])->name('plan.active')->middleware(['auth']);
    Route::get('/{uid}/notification/seen', [UserController::class, 'notificationSeen'])->name('notification.seen');

    // Email Templates
    Route::get('email_template_lang/{id}/{lang?}', [EmailTemplateController::class, 'manageEmailLang'])->name('manage.email.language')->middleware(['auth']);
    Route::any('email_template_store', [EmailTemplateController::class, 'updateStatus'])->name('status.email.language')->middleware(['auth']);
    Route::any('email_template_store/{pid}', [EmailTemplateController::class, 'storeEmailLang'])->name('store.email.language')->middleware(['auth']);
    Route::resource('email_template', EmailTemplateController::class)->middleware(['auth']);
    // End Email Templates
    //crm report
    Route::get('reports-lead', [ReportController::class, 'leadReport'])->name('report.lead')->middleware(['auth']);
    Route::get('reports-deal', [ReportController::class, 'dealReport'])->name('report.deal')->middleware(['auth']);
    // User Module

    Route::get('users/{view?}', [UserController::class, 'index'])->name('users')->middleware(['auth']);
    Route::get('users-view', [UserController::class, 'filterUserView'])->name('filter.user.view')->middleware(['auth']);
    Route::get('checkuserexists', [UserController::class, 'checkUserExists'])->name('user.exists')->middleware(['auth']);
    Route::get('profile', [UserController::class, 'profile'])->name('profile')->middleware(['auth']);
    Route::post('/profile', [UserController::class, 'updateProfile'])->name('update.profile')->middleware(['auth']);
    Route::get('user/info/{id}', [UserController::class, 'userInfo'])->name('users.info')->middleware(['auth']);
    Route::get('user/{id}/info/{type}', [UserController::class, 'getProjectTask'])->name('user.info.popup')->middleware(['auth']);
    // End User Module

    // Search
    Route::get('/search', [UserController::class, 'search'])->name('search.json');
    // end
    Route::get('dashboard-view', [DashboardController::class, 'filterView'])->name('dashboard.view')->middleware(['auth']);
    Route::get('dashboard', [DashboardController::class, 'clientView'])->name('client.dashboard.view')->middleware(['auth']);
    // saas
    Route::resource('users', UserController::class)->middleware(['auth']);
    Route::resource('plans', PlanController::class)->middleware(['auth']);
    Route::get('plan-trial/{id}', [PlanController::class, 'planTrial'])->name('plan.trial')->middleware(['auth']);
    Route::post('plan-disable', [PlanController::class, 'planDisable'])->name('plan.disable')->middleware(['auth']);
    Route::resource('coupons', CouponController::class)->middleware(['auth']);

    // Orders

    Route::group(
        [
            'middleware' => [
                'auth'
            ],
        ],
        function () {
            Route::get('/orders', [StripePaymentController::class, 'index'])->name('order.index');
            Route::get('/refund/{id}/{user_id}', [StripePaymentController::class, 'refund'])->name('order.refund');
            Route::get('/stripe/{code}', [StripePaymentController::class, 'stripe'])->name('stripe');
            Route::post('/stripe', [StripePaymentController::class, 'stripePost'])->name('stripe.post');
        }
    );

    Route::get('/apply-coupon', [CouponController::class, 'applyCoupon'])->name('apply.coupon')->middleware(['auth']);

    //================================= Form Builder ====================================//

    // Form Builder
    Route::resource('form_builder', FormBuilderController::class)->middleware(['auth']);

    // Form link base view


    // Form Field
    Route::get('/form_builder/{id}/field', [FormBuilderController::class, 'fieldCreate'])->name('form.field.create')->middleware(['auth']);
    Route::post('/form_builder/{id}/field', [FormBuilderController::class, 'fieldStore'])->name('form.field.store')->middleware(['auth']);
    Route::get('/form_builder/{id}/field/{fid}/show', [FormBuilderController::class, 'fieldShow'])->name('form.field.show')->middleware(['auth']);
    Route::get('/form_builder/{id}/field/{fid}/edit', [FormBuilderController::class, 'fieldEdit'])->name('form.field.edit')->middleware(['auth']);
    Route::post('/form_builder/{id}/field/{fid}', [FormBuilderController::class, 'fieldUpdate'])->name('form.field.update')->middleware(['auth']);
    Route::delete('/form_builder/{id}/field/{fid}', [FormBuilderController::class, 'fieldDestroy'])->name('form.field.destroy')->middleware(['auth']);

    // Form Response
    Route::get('/form_response/{id}', [FormBuilderController::class, 'viewResponse'])->name('form.response')->middleware(['auth']);
    Route::get('/response/{id}', [FormBuilderController::class, 'responseDetail'])->name('response.detail')->middleware(['auth']);

    // Form Field Bind
    Route::get('/form_field/{id}', [FormBuilderController::class, 'formFieldBind'])->name('form.field.bind')->middleware(['auth']);
    Route::post('/form_field_store/{id}}', [FormBuilderController::class, 'bindStore'])->name('form.bind.store')->middleware(['auth']);


    // Custom Landing Page

    //    Route::get('/landingpage', [LandingPageSectionController::class, 'index'])->name('custom_landing_page.index')->middleware(['auth']);
    //    Route::get('/LandingPage/show/{id}', [LandingPageSectionController::class, 'show']);
    //
    //    Route::post('/LandingPage/setConetent', [LandingPageSectionController::class, 'setConetent'])->middleware(['auth']);
    //
    //
    //    Route::get(
    //        '/get_landing_page_section/{name}', function ($name) {
    //        $plans = \DB::table('plans')->get();
    //
    //        return view('custom_landing_page.' . $name, compact('plans'));
    //    }
    //    );
    //
    //    Route::post('/LandingPage/removeSection/{id}', [LandingPageSectionController::class, 'removeSection'])->middleware(['auth']);
    //    Route::post('/LandingPage/setOrder', [LandingPageSectionController::class, 'setOrder'])->middleware(['auth']);
    //    Route::post('/LandingPage/copySection', [LandingPageSectionController::class, 'copySection'])->middleware(['auth']);

    // Plan Payment Gateways
    Route::post('plan-pay-with-bank', [BankTransferPaymentController::class, 'planPayWithBank'])->name('plan.pay.with.bank')->middleware(['auth']);

    Route::post('plan-pay-with-paypal', [PaypalController::class, 'planPayWithPaypal'])->name('plan.pay.with.paypal')->middleware(['auth']);
    Route::get('{id}/plan-get-payment-status', [PaypalController::class, 'planGetPaymentStatus'])->name('plan.get.payment.status')->middleware(['auth']);

    Route::post('/plan-pay-with-paystack', [PaystackPaymentController::class, 'planPayWithPaystack'])->name('plan.pay.with.paystack')->middleware(['auth']);
    Route::get('/plan/paystack/{pay_id}/{plan_id}', [PaystackPaymentController::class, 'getPaymentStatus'])->name('plan.paystack');

    Route::post('/plan-pay-with-flaterwave', [FlutterwavePaymentController::class, 'planPayWithFlutterwave'])->name('plan.pay.with.flaterwave')->middleware(['auth']);
    Route::get('/plan/flaterwave/{txref}/{plan_id}', [FlutterwavePaymentController::class, 'getPaymentStatus'])->name('plan.flaterwave');

    // ---------------------********************************-----------------------
    //plan-order
    Route::post('order/{id}/changeaction', [BankTransferPaymentController::class, 'changeStatus'])->name('order.changestatus');
    Route::delete('order/{id}', [BankTransferPaymentController::class, 'orderDestroy'])->name('order.destroy');
    Route::get('order/{id}/action', [BankTransferPaymentController::class, 'action'])->name('order.action');

    Route::group(
        [
            'middleware' => [
                'auth'
            ],
        ],
        function () {
            Route::get('support/{id}/reply', [SupportController::class, 'reply'])->name('support.reply');
            Route::post('support/{id}/reply', [SupportController::class, 'replyAnswer'])->name('support.reply.answer');
            Route::get('support/grid', [SupportController::class, 'grid'])->name('support.grid');
            Route::resource('support', SupportController::class);
        }
    );

    // Plan Request Module
    Route::get('plan_request', [PlanRequestController::class, 'index'])->name('plan_request.index')->middleware(['auth']);
    Route::get('request_frequency/{id}', [PlanRequestController::class, 'requestView'])->name('request.view')->middleware(['auth']);
    Route::get('request_send/{id}', [PlanRequestController::class, 'userRequest'])->name('send.request')->middleware(['auth']);
    Route::get('request_response/{id}/{response}', [PlanRequestController::class, 'acceptRequest'])->name('response.request')->middleware(['auth']);
    Route::get('request_cancel/{id}', [PlanRequestController::class, 'cancelRequest'])->name('request.cancel')->middleware(['auth']);
    //QR Code Module

    // Import/Export Data Route
    Route::get('export/customer', [CustomerController::class, 'export'])->name('customer.export');
    Route::get('import/customer/file', [CustomerController::class, 'importFile'])->name('customer.file.import');
    Route::post('import/customer', [CustomerController::class, 'customerImportdata'])->name('customer.import.data');
    Route::post('csv/import', [ImportController::class, 'fileImport'])->name('csv.import');
    Route::post('/customer/import', [CustomerController::class, 'directCustomerImport'])->name('customer.import');
    Route::get('import/csv/modal/', [ImportController::class, 'fileImportModal'])->name('csv.import.modal');
    // Route::post('import/customer', [CustomerController::class, 'import'])->name('customer.import');

    //Storage Setting

    Route::post('storage-settings', [SystemController::class, 'storageSettingStore'])->name('storage.setting.store')->middleware(['auth']);


    //NOC
 

    Route::post('setting/noc/{lang?}', [SystemController::class, 'NOCupdate'])->name('noc.update');
    Route::get('setting/noc', [SystemController::class, 'companyIndex'])->name('get.noc.language');
 

    Route::post('setting/google-calender', [SystemController::class, 'saveGoogleCalenderSettings'])->name('google.calender.settings');

    //User Log
    Route::get('/userlogs', [UserController::class, 'userLog'])->name('user.userlog')->middleware(['auth']);
    Route::get('userlogs/{id}', [UserController::class, 'userLogView'])->name('user.userlogview')->middleware(['auth']);
    Route::delete('userlogs/{id}', [UserController::class, 'userLogDestroy'])->name('user.userlogdestroy')->middleware(['auth']);

    //notification Template
    Route::get('notification_templates/{id?}/{lang?}', [NotificationTemplatesController::class, 'index'])->name('notification_templates.index')->middleware(['auth']);
    Route::get('notification-templates-lang/{id}/{lang?}', [NotificationTemplatesController::class, 'manageNotificationLang'])->name('manage.notification.language')->middleware(['auth']);
    Route::resource('notification-templates', NotificationTemplatesController::class)->middleware(['auth']);

    //Proposal/Invoice/Bill/Purchase/POS - footer notes
    Route::post('system-settings/note', [SystemController::class, 'footerNoteStore'])->name('system.settings.footernote')->middleware(['auth']);

    //AI module
    Route::post('chatgpt-settings', [SystemController::class, 'chatgptSetting'])->name('chatgpt.settings');
    Route::get('generate/{template_name}', [AiTemplateController::class, 'create'])->name('generate');
    Route::post('generate/keywords/{id}', [AiTemplateController::class, 'getKeywords'])->name('generate.keywords');
    Route::post('generate/response', [AiTemplateController::class, 'AiGenerate'])->name('generate.response');

    //AI module for grammar check
    Route::get('grammar/{template}', [AiTemplateController::class, 'grammar'])->name('grammar')->middleware(['auth']);
    Route::post('grammar/response', [AiTemplateController::class, 'grammarProcess'])->name('grammar.response')->middleware(['auth']);

    //IP-Restrication settings
    Route::get('create/ip', [SystemController::class, 'createIp'])->name('create.ip')->middleware(['auth']);
    Route::post('create/ip', [SystemController::class, 'storeIp'])->name('store.ip')->middleware(['auth']);
    Route::get('edit/ip/{id}', [SystemController::class, 'editIp'])->name('edit.ip')->middleware(['auth']);
    Route::post('edit/ip/{id}', [SystemController::class, 'updateIp'])->name('update.ip')->middleware(['auth']);
    Route::delete('destroy/ip/{id}', [SystemController::class, 'destroyIp'])->name('destroy.ip')->middleware(['auth']);

    //lang enable / disable
    Route::post('disable-language', [LanguageController::class, 'disableLang'])->name('disablelanguage')->middleware(['auth']);

    //Expense Module
    Route::get('expense/pdf/{id}', [ExpenseController::class, 'expense'])->name('expense.pdf');
    Route::group(
        [
            'middleware' => [
                'auth'
            ],
        ],
        function () {
            Route::any('expense/customer', [ExpenseController::class, 'customer'])->name('expense.customer');
            Route::post('expense/vender', [ExpenseController::class, 'vender'])->name('expense.vender');
            Route::post('expense/employee', [ExpenseController::class, 'employee'])->name('expense.employee');

            Route::post('expense/product/destroy', [ExpenseController::class, 'productDestroy'])->name('expense.product.destroy');

            Route::post('expense/product', [ExpenseController::class, 'product'])->name('expense.product');
            Route::get('expense/{id}/payment', [ExpenseController::class, 'payment'])->name('expense.payment');
            Route::get('expense/items', [ExpenseController::class, 'items'])->name('expense.items');

            Route::resource('expense', ExpenseController::class);
        }
    );

    Route::get('referral-program/company', [ReferralProgramController::class, 'companyIndex'])->name('referral-program.company');
    Route::resource('referral-program', ReferralProgramController::class);
    Route::get('request-amount-sent/{id}', [ReferralProgramController::class, 'requestedAmountSent'])->name('request.amount.sent');
    Route::get('request-amount-cancel/{id}', [ReferralProgramController::class, 'requestCancel'])->name('request.amount.cancel');
    Route::post('request-amount-store/{id}', [ReferralProgramController::class, 'requestedAmountStore'])->name('request.amount.store');
    Route::get('request-amount/{id}/{status}', [ReferralProgramController::class, 'requestedAmount'])->name('amount.request');
});


Route::any('/cookie-consent', [SystemController::class, 'CookieConsent'])->name('cookie-consent');
