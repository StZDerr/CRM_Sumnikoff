<?php

use App\Http\Controllers\AttendanceController;
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
use App\Http\Controllers\OperationController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\PaymentCategoryController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectCommentController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SpecialtyController;
use App\Http\Controllers\StageController;
use App\Http\Controllers\RegDomainController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VacationController;
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
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

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
    ]);

    Route::post('vacations/{vacation}/end', [VacationController::class, 'end'])->name('vacations.end');

    Route::get('users/{user}/dashboard', [UserController::class, 'userDashboard'])->name('user.dashboard');
    Route::get('reg/domains', [RegDomainController::class, 'index'])
        ->name('reg.domains.index')
        ->middleware('admin');
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

    Route::delete('projects/{project}/comments/{comment}', [ProjectCommentController::class, 'destroy'])
        ->name('projects.comments.destroy');

    Route::patch('projects/{project}/comments/{comment}', [ProjectCommentController::class, 'update'])
        ->name('projects.comments.update');

    Route::get('calendar', [CalendarController::class, 'allProjects'])->name('calendar.all-projects');

    Route::get('projects/{project}/calendar', [CalendarController::class, 'index'])->name('calendar.index');

    Route::post('expense-categories/reorder', [ExpenseCategoryController::class, 'reorder'])->name('expense-categories.reorder');

    // Быстрое создание офисного расхода
    Route::post('expenses/store-office', [ExpenseController::class, 'storeOffice'])->name('expenses.store-office');

    // Быстрое создание зарплатного расхода
    Route::post('expenses/store-salary', [ExpenseController::class, 'storeSalary'])->name('expenses.store-salary');

    // Выплата аванса из табеля
    Route::post('expenses/store-advance', [ExpenseController::class, 'storeAdvance'])->name('expenses.store-advance');

    // Полная выплата зарплаты из табеля
    Route::post('expenses/store-final-salary', [ExpenseController::class, 'storeFinalSalary'])->name('expenses.store-final-salary');

    // Получить проекты организации в JSON
    Route::get('organizations/{organization}/projects', [OrganizationController::class, 'projectsList'])
        ->name('organizations.projects');

    // Удаление прикреплённого документа
    Route::delete('documents/{document}', [DocumentController::class, 'destroy'])
        ->name('documents.destroy');

    // AJAX: Создание организации из модального окна
    Route::post('organizations/store-ajax', [OrganizationController::class, 'storeAjax'])
        ->name('organizations.store.ajax');

    Route::post('invoice-statuses/reorder', [InvoiceStatusController::class, 'reorder'])->name('invoice-statuses.reorder');

    Route::get('operation', [OperationController::class, 'index'])
        ->name('operation.index');

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

    Route::get('attendance/{user}', [AttendanceController::class, 'userShow'])->name('attendance.userShow');

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
