<?php

use App\Http\Controllers\CalendarController;
use App\Http\Controllers\CampaignSourceController;
use App\Http\Controllers\CampaignStatusController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ExpenseCategoryController;
use App\Http\Controllers\ImportanceController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectCommentController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\StageController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
})->middleware(['auth', 'verified']);

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

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
    ]);
    // endpoint для сохранения порядка (название: importances.reorder)
    Route::post('importances/reorder', [ImportanceController::class, 'reorder'])->name('importances.reorder');

    Route::post('stages/reorder', [StageController::class, 'reorder'])->name('stages.reorder');

    Route::post('payment-methods/reorder', [PaymentMethodController::class, 'reorder'])->name('payment-methods.reorder');

    Route::post('campaign-statuses/reorder', [CampaignStatusController::class, 'reorder'])->name('campaign-statuses.reorder');

    Route::post('campaign-sources/reorder', [CampaignSourceController::class, 'reorder'])->name('campaign-sources.reorder');

    Route::get('projects/{project}/comments', [ProjectCommentController::class, 'index'])
        ->name('projects.comments.index');

    Route::post('projects/{project}/comments', [ProjectCommentController::class, 'store'])
        ->name('projects.comments.store');

    Route::delete('projects/{project}/comments/{comment}', [ProjectCommentController::class, 'destroy'])
        ->name('projects.comments.destroy');

    Route::get('projects/{project}/calendar', [CalendarController::class, 'index'])->name('calendar.index');

    Route::post('expense-categories/reorder', [ExpenseCategoryController::class, 'reorder'])->name('expense-categories.reorder');

});

require __DIR__.'/auth.php';
