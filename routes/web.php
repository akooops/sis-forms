<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardContoller;
use App\Http\Controllers\Admin\FilesController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\PermissionsController;
use App\Http\Controllers\Admin\RolesController;
use App\Http\Controllers\Admin\UsersController;
use App\Http\Controllers\SubmissionsController as ControllersSubmissionsController;
use App\Http\Controllers\Admin\SubmissionsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| Admin Authentication Routes
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->middleware(['force.admin.english', 'handle.inertia'])->group(function () {
    Route::get('auth/login', [AuthController::class, 'showLoginForm'])->middleware('guest')->name('admin.auth.login');
    Route::post('auth/login', [AuthController::class, 'login'])->middleware('guest')->name('admin.auth.login');
    
    // Azure AD authentication routes
    Route::get('auth/azure', [AuthController::class, 'redirectToAzure'])->middleware('guest')->name('admin.auth.azure');
    Route::get('auth/azure/callback', [AuthController::class, 'handleAzureCallback'])->name('admin.auth.azure.callback');
});

Route::middleware(['auth', 'force.admin.english', 'handle.inertia'])->prefix('admin')->group(function () {
    Route::get('/', [DashboardContoller::class, 'index'])->middleware('check.permission:admin.dashboard.index')->name('admin.dashboard');

    //Auth
    Route::post('auth/logout', [AuthController::class, 'logout'])->name('admin.auth.logout');

    // Permissions
    Route::get('permissions', [PermissionsController::class, 'index'])->middleware('check.permission:admin.permissions.index')->name('admin.permissions.index');

    // Roles
    Route::get('roles', [RolesController::class, 'index'])->middleware('check.permission:admin.roles.index')->name('admin.roles.index');
    Route::get('roles/create', [RolesController::class, 'create'])->middleware('check.permission:admin.roles.store')->name('admin.roles.create');
    Route::post('roles', [RolesController::class, 'store'])->middleware('check.permission:admin.roles.store')->name('admin.roles.store');
    Route::get('roles/{role}', [RolesController::class, 'show'])->middleware('check.permission:admin.roles.show')->name('admin.roles.show');
    Route::get('roles/{role}/edit', [RolesController::class, 'edit'])->middleware('check.permission:admin.roles.update')->name('admin.roles.edit');
    Route::patch('roles/{role}', [RolesController::class, 'update'])->middleware('check.permission:admin.roles.update')->name('admin.roles.update');
    Route::delete('roles/{role}', [RolesController::class, 'destroy'])->middleware('check.permission:admin.roles.destroy')->name('admin.roles.destroy');

    // Users
    Route::get('users', [UsersController::class, 'index'])->middleware('check.permission:admin.users.index')->name('admin.users.index');
    Route::get('users/create', [UsersController::class, 'create'])->middleware('check.permission:admin.users.store')->name('admin.users.create');
    Route::post('users', [UsersController::class, 'store'])->middleware('check.permission:admin.users.store')->name('admin.users.store');
    Route::get('users/{user}', [UsersController::class, 'show'])->middleware('check.permission:admin.users.show')->name('admin.users.show');
    Route::get('users/{user}/edit', [UsersController::class, 'edit'])->middleware('check.permission:admin.users.update')->name('admin.users.edit');
    Route::patch('users/{user}', [UsersController::class, 'update'])->middleware('check.permission:admin.users.update')->name('admin.users.update');
    Route::delete('users/{user}', [UsersController::class, 'destroy'])->middleware('check.permission:admin.users.destroy')->name('admin.users.destroy');

    // Files
    Route::post('files/upload', [FilesController::class, 'upload'])->name('admin.files.upload');

    // Notifications  
    Route::get('notifications', [NotificationController::class, 'index'])->middleware('check.permission:admin.notifications.index')->name('admin.notifications.index');
    Route::get('notifications/unread-count', [NotificationController::class, 'unreadCount'])->middleware('check.permission:admin.notifications.index')->name('admin.notifications.unread-count');
    Route::patch('notifications/{notification}/mark-read', [NotificationController::class, 'markAsRead'])->middleware('check.permission:admin.notifications.index')->name('admin.notifications.mark-read');
    Route::patch('notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->middleware('check.permission:admin.notifications.index')->name('admin.notifications.mark-all-read');

    // Submissions
    Route::get('submissions', [SubmissionsController::class, 'index'])->middleware('check.permission:admin.submissions.index')->name('admin.submissions.index');
    Route::get('submissions/export', [SubmissionsController::class, 'export'])->middleware('check.permission:admin.submissions.index')->name('admin.submissions.export');
    Route::delete('submissions/{submission}', [SubmissionsController::class, 'destroy'])->middleware('check.permission:admin.submissions.destroy')->name('admin.submissions.destroy');
});

Route::middleware(['set.locale', 'handle.inertia'])->group(function () {
    Route::get('', [ControllersSubmissionsController::class, 'index'])->name('submissions.index');
    Route::post('', [ControllersSubmissionsController::class, 'store'])->name('submissions.store');
});

// Language switching routes
Route::get('/lang/{locale}', function ($locale) {
    if (in_array($locale, ['en', 'ar'])) {
        session(['locale' => $locale]);
        app()->setLocale($locale);
    }
    return redirect()->back();
})->name('lang.switch');
