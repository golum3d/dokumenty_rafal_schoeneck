<?php

use App\Http\Controllers\DocumentCategoryController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\DocumentStatusController;
use App\Http\Controllers\FolderController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WebAuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [WebAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [WebAuthController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [WebAuthController::class, 'dashboard'])->name('dashboard');

    Route::middleware('can:manage-users')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');

        Route::get('/admin/document-categories', [DocumentCategoryController::class, 'index'])->name('documents.categories.index');
        Route::get('/admin/document-categories/{category}/edit', [DocumentCategoryController::class, 'edit'])->name('documents.categories.edit');
        Route::post('/admin/document-categories', [DocumentCategoryController::class, 'store'])->name('documents.categories.store');
        Route::put('/admin/document-categories/{category}', [DocumentCategoryController::class, 'update'])->name('documents.categories.update');
        Route::delete('/admin/document-categories/{category}', [DocumentCategoryController::class, 'destroy'])->name('documents.categories.destroy');

        Route::get('/admin/document-statuses', [DocumentStatusController::class, 'index'])->name('documents.statuses.index');
        Route::get('/admin/document-statuses/{status}/edit', [DocumentStatusController::class, 'edit'])->name('documents.statuses.edit');
        Route::post('/admin/document-statuses', [DocumentStatusController::class, 'store'])->name('documents.statuses.store');
        Route::put('/admin/document-statuses/{status}', [DocumentStatusController::class, 'update'])->name('documents.statuses.update');
        Route::delete('/admin/document-statuses/{status}', [DocumentStatusController::class, 'destroy'])->name('documents.statuses.destroy');
    });

    Route::middleware('can:manage-documents')->group(function () {
        Route::get('/documents', [DocumentController::class, 'index'])->name('documents.index');
        Route::get('/documents/create', [DocumentController::class, 'create'])->name('documents.create');
        Route::post('/documents', [DocumentController::class, 'store'])->name('documents.store');
        Route::get('/documents/{document}/edit', [DocumentController::class, 'edit'])->name('documents.edit');
        Route::put('/documents/{document}', [DocumentController::class, 'update'])->name('documents.update');
        Route::get('/documents/{document}/preview', [DocumentController::class, 'preview'])->name('documents.preview');
        Route::get('/documents/{document}/file', [DocumentController::class, 'file'])->name('documents.file');
        Route::get('/documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');

        Route::post('/folders', [FolderController::class, 'store'])->name('folders.store');
        Route::put('/folders/{folder}', [FolderController::class, 'update'])->name('folders.update');
        Route::delete('/folders/{folder}', [FolderController::class, 'destroy'])->name('folders.destroy');
    });

    Route::post('/logout', [WebAuthController::class, 'logout'])->name('logout');
});
