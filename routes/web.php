<?php

use App\Http\Controllers\BankAccountController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\CampaignSourceController;
use App\Http\Controllers\CampaignStatusController;
use App\Http\Controllers\ContactController;
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
use App\Http\Controllers\StageController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VacationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
})->middleware(['auth', 'verified']);

Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

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
    ]);

    Route::post('vacations/{vacation}/end', [VacationController::class, 'end'])->name('vacations.end');

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

    // Получить проекты организации в JSON
    Route::get('organizations/{organization}/projects', [OrganizationController::class, 'projectsList'])
        ->name('organizations.projects');

    Route::post('invoice-statuses/reorder', [InvoiceStatusController::class, 'reorder'])->name('invoice-statuses.reorder');

    Route::get('operation', [OperationController::class, 'index'])
        ->name('operation.index');

    // Получить счета проекта в JSON
    Route::get('projects/{project}/invoices', [InvoiceController::class, 'invoicesByProject'])
        ->name('projects.invoices');

    // внутри middleware('auth') группы
    Route::get('search', [SearchController::class, 'index'])->name('search');
});

require __DIR__.'/auth.php';
