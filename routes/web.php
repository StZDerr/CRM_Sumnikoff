<?php

use App\Http\Controllers\AccountCredentialController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AvitoController;
use App\Http\Controllers\BankAccountController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\CampaignSourceController;
use App\Http\Controllers\CampaignStatusController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ExpenseCategoryController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ImportanceController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\InvoiceStatusController;
use App\Http\Controllers\LinkCardController;
use App\Http\Controllers\MonthlyExpenseController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OperationController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\PaymentCategoryController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectCommentController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\RegDomainController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SpecialtyController;
use App\Http\Controllers\StageController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VacationController;
use App\Http\Controllers\WorkTimeController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
})->middleware(['auth', 'verified']);

Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Welcome page for non-admin users (маркетологи / PM)
Route::get('/welcome', [\App\Http\Controllers\DashboardController::class, 'welcome'])
    ->middleware(['auth', 'verified'])
    ->name('welcome');

Route::middleware('auth')->group(function () {

    Route::get('avito', [AvitoController::class, 'index'])->name('avito.index');
    Route::post('avito/accounts', [AvitoController::class, 'store'])->name('avito.accounts.store');
    Route::post('avito/accounts/{avitoAccount}/sync', [AvitoController::class, 'sync'])->name('avito.accounts.sync');
    Route::post('avito/accounts/sync-all', [AvitoController::class, 'syncAll'])->name('avito.accounts.sync-all');
    Route::post('avito/accounts/{avitoAccount}/attach-project', [AvitoController::class, 'attachProject'])->name('avito.accounts.attach-project');
    Route::post('avito/accounts/{avitoAccount}/notification-settings', [AvitoController::class, 'updateNotificationSettings'])->name('avito.accounts.notification-settings');

    // Dev welcome page for front-end developers (controller action)
    Route::get('/dev', [\App\Http\Controllers\DashboardController::class, 'dev'])
        ->name('dev')
        ->middleware('role:frontend');

    Route::prefix('work-time')->name('work-time.')->group(function () {
        Route::get('state', [WorkTimeController::class, 'state'])->name('state');
        Route::post('start-day', [WorkTimeController::class, 'startDay'])->name('start-day');
        Route::post('start-break', [WorkTimeController::class, 'startBreak'])->name('start-break');
        Route::post('end-break', [WorkTimeController::class, 'endBreak'])->name('end-break');
        Route::post('save-report', [WorkTimeController::class, 'saveReport'])->name('save-report');
        Route::post('end-day', [WorkTimeController::class, 'endDay'])->name('end-day');

        Route::patch('work-days/{workDay}/end-time', [WorkTimeController::class, 'editDayEnd'])->name('work-days.end-time');
        Route::patch('work-days/{workDay}/start-time', [WorkTimeController::class, 'editDayStart'])->name('work-days.start-time');
        Route::post('work-days/{workDay}/breaks', [WorkTimeController::class, 'addBreak'])->name('work-days.breaks.store');

        Route::patch('breaks/{workBreak}', [WorkTimeController::class, 'updateBreak'])->name('breaks.update');
        Route::delete('breaks/{workBreak}', [WorkTimeController::class, 'deleteBreak'])->name('breaks.delete');
    });

    Route::get('projects/arrears', [ProjectController::class, 'arrears'])->name('projects.arrears');
    Route::get('projects/debtors', [ProjectController::class, 'debtors'])->name('projects.debtors');

    // Отправка проекта юристу (admin / pm)
    Route::post('projects/{project}/send-to-lawyer', [\App\Http\Controllers\ProjectLawyerController::class, 'store'])->name('projects.send-to-lawyer');

    // Сторона юриста: список и подробности
    Route::get('lawyer/projects', [\App\Http\Controllers\ProjectLawyerController::class, 'index'])->name('lawyer.projects.index');
    Route::get('lawyer/projects/{projectLawyer}', [\App\Http\Controllers\ProjectLawyerController::class, 'show'])->name('lawyer.projects.show');
    Route::patch('lawyer/projects/{projectLawyer}', [\App\Http\Controllers\ProjectLawyerController::class, 'update'])->name('lawyer.projects.update');

    // Просмотр проекта/организации конкретно для юриста (доступ только к отправленным ему)
    Route::get('lawyer/projects/{projectLawyer}/project', [\App\Http\Controllers\ProjectLawyerController::class, 'project'])->name('lawyer.projects.project');
    Route::get('lawyer/projects/{projectLawyer}/organization', [\App\Http\Controllers\ProjectLawyerController::class, 'organization'])->name('lawyer.projects.organization');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('users/deleted', [UserController::class, 'deleted'])->name('users.deleted');
    Route::post('users/{user}/restore', [UserController::class, 'restore'])->name('users.restore')->withTrashed();
    Route::get('users/work-states', [UserController::class, 'workStates'])->name('users.workStates');

    Route::resources([
        'users' => UserController::class,
        'importances' => ImportanceController::class,
        'stages' => StageController::class,
        'payment-methods' => PaymentMethodController::class,
        'campaign-statuses' => CampaignStatusController::class,
        'campaign-sources' => CampaignSourceController::class,
        'contacts' => ContactController::class,
        'organizations' => OrganizationController::class,
        'projects' => ProjectController::class,
        'invoices' => InvoiceController::class,
        'payments' => PaymentController::class,
        'expense-categories' => ExpenseCategoryController::class,
        'bank-accounts' => BankAccountController::class,
        'expenses' => ExpenseController::class,
        'invoice-statuses' => InvoiceStatusController::class,
        'payment-categories' => PaymentCategoryController::class,
        'vacations' => VacationController::class,
        'specialties' => SpecialtyController::class,
        // 'account-credentials' => AccountCredentialController::class,
    ]);

    // Admin-only endpoint to update project importance quickly from the list
    Route::patch('projects/{project}/importance', [ProjectController::class, 'updateImportance'])
        ->name('projects.importance.update');

    Route::prefix('projects/{project}')->group(function () {
        Route::get('history', [ProjectController::class, 'userHistory'])->name('projects.userHistory');
        Route::patch('history/{history}', [ProjectController::class, 'updateHistory'])->name('projects.history.update');
        Route::delete('history/{history}', [ProjectController::class, 'destroyHistory'])->name('projects.history.destroy');
        Route::get('account-credentials', [AccountCredentialController::class, 'index'])
            ->name('account-credentials.index');

        Route::get('account-credentials/create', [AccountCredentialController::class, 'create'])
            ->name('account-credentials.create');

        Route::get('account-credentials/createSite', [AccountCredentialController::class, 'createSite'])
            ->name('account-credentials.createSite');

        Route::post('account-credentials/storeSite', [AccountCredentialController::class, 'storeSite'])
            ->name('account-credentials.storeSite');

        Route::get('account-credentials/createBD', [AccountCredentialController::class, 'createBD'])
            ->name('account-credentials.createBD');

        Route::post('account-credentials/storeBD', [AccountCredentialController::class, 'storeBD'])
            ->name('account-credentials.storeBD');

        Route::get('account-credentials/createSSH', [AccountCredentialController::class, 'createSSH'])
            ->name('account-credentials.createSSH');

        Route::post('account-credentials/storeSSH', [AccountCredentialController::class, 'storeSSH'])
            ->name('account-credentials.storeSSH');

        Route::get('account-credentials/createFTP', [AccountCredentialController::class, 'createFTP'])
            ->name('account-credentials.createFTP');

        Route::post('account-credentials/storeFTP', [AccountCredentialController::class, 'storeFTP'])
            ->name('account-credentials.storeFTP');

        Route::post('account-credentials', [AccountCredentialController::class, 'store'])
            ->name('account-credentials.store');

        Route::get('account-credentials/createOther', [AccountCredentialController::class, 'createOther'])
            ->name('account-credentials.createOther');

        Route::post('account-credentials/storeOther', [AccountCredentialController::class, 'storeOther'])
            ->name('account-credentials.storeOther');

    });

    Route::get('account-credentials/itSumnikoff', [AccountCredentialController::class, 'itSumnikoff'])
        ->name('account-credentials.itSumnikoff');

    // IT Sumnikoff (shared credentials)
    Route::get('account-credentials/createItSumnikoff', [AccountCredentialController::class, 'createItSumnikoff'])
        ->name('account-credentials.createItSumnikoff');
    Route::post('account-credentials/storeItSumnikoff', [AccountCredentialController::class, 'storeItSumnikoff'])
        ->name('account-credentials.storeItSumnikoff');

    // Show / Edit for IT Sumnikoff items (restricted to admin / project_manager)
    Route::get('account-credentials/itSumnikoff/{accountCredential}', [AccountCredentialController::class, 'showItSumnikoff'])
        ->name('account-credentials.showItSumnikoff');
    Route::get('account-credentials/itSumnikoff/{accountCredential}/edit', [AccountCredentialController::class, 'editItSumnikoff'])
        ->name('account-credentials.editItSumnikoff');
    Route::get('account-credentials/itSumnikoff/edit/{accountCredential}', [AccountCredentialController::class, 'editItSumnikoff'])
        ->name('account-credentials.editItSumnikoff');
    Route::put('account-credentials/itSumnikoff/{accountCredential}', [AccountCredentialController::class, 'updateItSumnikoff'])
        ->name('account-credentials.updateItSumnikoff');
    Route::get('account-credentials/{accountCredential}', [AccountCredentialController::class, 'show'])
        ->name('account-credentials.show');

    // Запись действий с доступом (просмотр/раскрытие/копирование)
    Route::post('account-credentials/{accountCredential}/access-log', [AccountCredentialController::class, 'accessLog'])
        ->name('account-credentials.accessLog');

    Route::get('account-credentials/{accountCredential}/edit', [AccountCredentialController::class, 'edit'])
        ->name('account-credentials.edit');
    Route::put('account-credentials/{accountCredential}', [AccountCredentialController::class, 'update'])
        ->name('account-credentials.update');
    Route::delete('account-credentials/{accountCredential}', [AccountCredentialController::class, 'destroy'])
        ->name('account-credentials.destroy');

    Route::post('vacations/{vacation}/end', [VacationController::class, 'end'])->name('vacations.end');

    Route::get('users/{user}/dashboard', [UserController::class, 'userDashboard'])->name('user.dashboard');
    Route::get('reg/domains', [RegDomainController::class, 'index'])
        ->name('domains.index');
    Route::get('reg/domains/create', [RegDomainController::class, 'create'])
        ->name('domains.create');
    Route::post('reg/domains', [RegDomainController::class, 'store'])
        ->name('domains.store');
    Route::post('reg/domains/sync', [RegDomainController::class, 'sync'])
        ->name('domains.sync');
    Route::post('reg/domains/{domain}/renew', [RegDomainController::class, 'renew'])
        ->name('domains.renew');
    Route::get('reg/domains/{domain}/edit', [RegDomainController::class, 'edit'])
        ->name('domains.edit');
    Route::put('reg/domains/{domain}', [RegDomainController::class, 'update'])
        ->name('domains.update');
    Route::delete('reg/domains/{domain}', [RegDomainController::class, 'destroy'])
        ->name('domains.destroy');
    // Получить отпуска пользователя (HTML partial для offcanvas)
    Route::get('users/{user}/vacations', [VacationController::class, 'userVacations'])->name('users.vacations');
    // endpoint для сохранения порядка (название: importances.reorder)
    Route::post('importances/reorder', [ImportanceController::class, 'reorder'])->name('importances.reorder');

    Route::post('stages/reorder', [StageController::class, 'reorder'])->name('stages.reorder');

    Route::post('payment-categories/reorder', [PaymentCategoryController::class, 'reorder'])->name('payment-categories.reorder');

    Route::post('payment-methods/reorder', [PaymentMethodController::class, 'reorder'])->name('payment-methods.reorder');

    Route::post('campaign-statuses/reorder', [CampaignStatusController::class, 'reorder'])->name('campaign-statuses.reorder');

    Route::post('campaign-sources/reorder', [CampaignSourceController::class, 'reorder'])->name('campaign-sources.reorder');

    Route::get('projects/{project}/comments', [ProjectCommentController::class, 'index'])
        ->name('projects.comments.index');

    Route::post('projects/{project}/comments', [ProjectCommentController::class, 'store'])
        ->name('projects.comments.store');

    // Comments for lawyer (stored separately)
    Route::post('projects/{project}/lawyer-comments', [\App\Http\Controllers\ProjectLawyerCommentController::class, 'store'])
        ->name('projects.lawyer-comments.store');

    Route::delete('projects/{project}/comments/{comment}', [ProjectCommentController::class, 'destroy'])
        ->name('projects.comments.destroy');

    // Удаление отдельного файла (фото/документ) из комментария
    Route::delete('projects/{project}/comments/{comment}/files/{photo}', [ProjectCommentController::class, 'destroyFile'])
        ->name('projects.comments.files.destroy');

    Route::patch('projects/{project}/comments/{comment}', [ProjectCommentController::class, 'update'])
        ->name('projects.comments.update');

    Route::get('calendar', [CalendarController::class, 'allProjects'])->name('calendar.all-projects');

    Route::get('projects/{project}/calendar', [CalendarController::class, 'index'])->name('calendar.index');

    Route::post('expense-categories/reorder', [ExpenseCategoryController::class, 'reorder'])->name('expense-categories.reorder');

    // Быстрое создание офисного расхода
    Route::post('expenses/store-office', [ExpenseController::class, 'storeOffice'])->name('expenses.store-office');

    // Быстрое создание зарплатного расхода
    Route::post('expenses/store-salary', [ExpenseController::class, 'storeSalary'])->name('expenses.store-salary');

    // Быстрое создание расхода домены/хостинг
    Route::post('expenses/store-domain-hosting', [ExpenseController::class, 'storeDomainHosting'])->name('expenses.store-domain-hosting');

    // Быстрое создание расхода "не наши" (без метода оплаты и банковского счёта)
    Route::post('expenses/store-not-our', [ExpenseController::class, 'storeNotOur'])->name('expenses.store-not-our');

    Route::post('monthly-expenses/{monthlyExpense}/pay', [MonthlyExpenseController::class, 'pay'])
        ->name('monthly-expenses.pay');

    // Пометить как оплаченный без создания расхода
    Route::post('monthly-expenses/{monthlyExpense}/mark-paid', [MonthlyExpenseController::class, 'markPaidOnly'])
        ->name('monthly-expenses.mark-paid');

    Route::resource('monthly-expenses', MonthlyExpenseController::class)->except(['show']);

    // Link cards
    Route::post('link-cards', [LinkCardController::class, 'store'])->name('link-cards.store');
    Route::match(['put', 'patch'], 'link-cards/{linkCard}', [LinkCardController::class, 'update'])->name('link-cards.update');
    Route::post('link-cards/reorder', [LinkCardController::class, 'reorder'])->name('link-cards.reorder');
    Route::delete('link-cards/{linkCard}', [LinkCardController::class, 'destroy'])->name('link-cards.destroy');

    // Выплата аванса из табеля
    Route::post('expenses/store-advance', [ExpenseController::class, 'storeAdvance'])->name('expenses.store-advance');

    // Полная выплата зарплаты из табеля
    Route::post('expenses/store-final-salary', [ExpenseController::class, 'storeFinalSalary'])->name('expenses.store-final-salary');

    // Получить проекты организации в JSON
    Route::get('organizations/{organization}/projects', [OrganizationController::class, 'projectsList'])
        ->name('organizations.projects');

    // Быстрое добавление документа для организации
    Route::post('organizations/{organization}/documents', [OrganizationController::class, 'storeDocument'])
        ->name('organizations.documents.store');

    // Удаление прикреплённого документа
    Route::delete('documents/{document}', [DocumentController::class, 'destroy'])
        ->name('documents.destroy');

    // Скачивание документа (с оригинальным именем и mime)
    Route::get('documents/{document}/download', [DocumentController::class, 'download'])
        ->name('documents.download');

    // AJAX: Создание организации из модального окна
    Route::post('organizations/store-ajax', [OrganizationController::class, 'storeAjax'])
        ->name('organizations.store.ajax');

    Route::post('invoice-statuses/reorder', [InvoiceStatusController::class, 'reorder'])->name('invoice-statuses.reorder');

    Route::get('operation', [OperationController::class, 'index'])
        ->name('operation.index');

    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::patch('notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::patch('notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::get('notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
    Route::delete('notifications/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');

    // Получить счета проекта в JSON
    Route::get('projects/{project}/invoices', [InvoiceController::class, 'invoicesByProject'])
        ->name('projects.invoices');

    Route::get('attendance/index', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('attendance/store', [AttendanceController::class, 'store'])->name('attendance.store');

    // Список табелей на согласование
    Route::get('/attendance/approvals', [AttendanceController::class, 'approvals'])
        ->name('attendance.approvals')
        ->middleware('auth'); // при необходимости можно добавить middleware для роли начальника

    // Список табелей на аванс
    Route::get('/attendance/advance', [AttendanceController::class, 'advance'])
        ->name('attendance.advance')
        ->middleware('auth'); // при необходимости можно добавить middleware для роли бугалтера

    // Список табелей на оплату полностью
    Route::get('/attendance/payable', [AttendanceController::class, 'payable'])
        ->name('attendance.payable')
        ->middleware('auth'); // при необходимости можно добавить middleware для роли бугалтера

    // Список табелей архив оплаченных
    Route::get('/attendance/paid', [AttendanceController::class, 'paid'])
        ->name('attendance.paid')
        ->middleware('auth'); // при необходимости можно добавить middleware для роли начальника

    // Список табелей на согласование
    Route::get('/attendance/rejected', [AttendanceController::class, 'rejected'])
        ->name('attendance.rejected')
        ->middleware('auth'); // при необходимости можно добавить middleware для роли начальника

    Route::get('attendance/{user}', [AttendanceController::class, 'userShow'])->name('attendance.userShow')->withTrashed();

    Route::get('rejected/{report}', [AttendanceController::class, 'rejectedUserShow'])->name('rejected.userShow');

    // Отправка табеля на согласование
    Route::post('/attendance/{user}/submit', [AttendanceController::class, 'submitForApproval'])
        ->name('attendance.submit')
        ->middleware('auth'); // при необходимости, чтобы только авторизованные пользователи могли отправлять

    // Показ табеля конкретного пользователя
    Route::get('/attendance/show/{report}', [AttendanceController::class, 'show'])
        ->name('attendance.show')
        ->middleware('auth');

    // Одобрение табеля начальством
    Route::post('/attendance/{report}/approve', [AttendanceController::class, 'approve'])
        ->name('attendance.approve')
        ->middleware('auth');

    // Оплата табеля
    Route::post('/attendance/{report}/paidUpdate', [AttendanceController::class, 'paidUpdate'])
        ->name('attendance.paidUpdate')
        ->middleware('auth');

    // Отклонение табеля начальством
    Route::post('/attendance/{report}/reject', [AttendanceController::class, 'reject'])
        ->name('attendance.reject')
        ->middleware('auth');

    // Обновление существующего табеля
    Route::put('/attendance/{report}/update', [AttendanceController::class, 'update'])
        ->name('attendance.update')
        ->middleware('auth');

    // Обновление комментария табеля (краткий endpoint)
    Route::post('/attendance/{report}/comment', [AttendanceController::class, 'updateComment'])
        ->name('attendance.comment')
        ->middleware('auth');

    // внутри middleware('auth') группы
    Route::get('search', [SearchController::class, 'index'])->name('search');
});

require __DIR__.'/auth.php';
