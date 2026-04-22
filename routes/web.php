<?php

use App\Http\Controllers\Frontend\AuthorController;
use App\Http\Controllers\Frontend\BookmarkController;
use App\Http\Controllers\Frontend\ClapController;
use App\Http\Controllers\Frontend\FollowController;
use App\Http\Controllers\Frontend\FrontendProfileController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CommentController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\TagController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\UserRoleController;
// Frontend Controllers
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\BlogController;
use App\Http\Controllers\Frontend\CategoryPageController;
use App\Http\Controllers\Frontend\TagPageController;

// Frontend routes
Route::get('/',                         [HomeController::class,         'index'])->name('home');
Route::get('/blog',                     [BlogController::class,         'index'])->name('blog');
Route::get('/blog/{slug}',              [BlogController::class,         'show'])->name('blog.post');
Route::get('/category/{slug}',          [CategoryPageController::class, 'show'])->name('blog.category');
Route::get('/tag/{slug}',               [TagPageController::class,      'show'])->name('blog.tag');

Route::get('/authors/{username}',  [AuthorController::class,       'show'])->name('author.profile');

Route::middleware(['auth', 'verified'])->group(function () {
    // Comments
    Route::post('/comments', [BlogController::class, 'storeComment'])->name('comments.store');
    Route::delete('/comments/{comment}', [BlogController::class, 'destroyComment'])->name('comments.destroy');

    // Claps
    Route::post('/posts/{post}/clap', [ClapController::class, 'clap'])->name('posts.clap');

    // Bookmarks
    Route::post('/bookmarks',  [BookmarkController::class, 'toggle'])->name('bookmarks.toggle');
    Route::get('/bookmarks', [BookmarkController::class, 'index'])->name('bookmarks.index');

    // Follows
    Route::post('/authors/{author}/follow', [FollowController::class, 'toggle'])->name('authors.follow');
    Route::get('/following', [FollowController::class, 'following'])->name('following.index');
    Route::get('/followers', [FollowController::class, 'followers'])->name('followers.index');

});

Route::middleware(['auth'])->name('frontend.')->group(function () {
    // Frontend profile routes
    Route::get('/profile',           [FrontendProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit',      [FrontendProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile',           [FrontendProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password',  [FrontendProfileController::class, 'updatePassword'])->name('profile.password');
    Route::delete('/profile/avatar', [FrontendProfileController::class, 'removeAvatar'])->name('profile.avatar.remove');
});

// Auth Routes (Breeze handles login/register/etc.)
require __DIR__.'/auth.php';




// Admin Panel Routes — must be authenticated
Route::middleware(['auth', 'verified', 'admin.access'])->prefix('admin')->name('admin.')->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        // route name = admin.dashboard

        // Admin Profile Routes — prefixed to avoid collision with frontend profile
        Route::get('/profile',    [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile',  [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
        Route::put('/password',   [ProfileController::class, 'updatePassword'])->name('profile.password');
        Route::delete('/avatar',  [ProfileController::class, 'removeAvatar'])->name('profile.avatar.remove');
        // route names = admin.profile.edit, admin.profile.update, etc.

        Route::middleware(['permission:access admin panel'])->group(function () {

            // Trash Routes
            Route::get('posts/trash',              [PostController::class, 'trash'])->name('posts.trash');
            Route::patch('posts/{id}/restore',     [PostController::class, 'restore'])->name('posts.restore');
            Route::delete('posts/{id}/force-delete', [PostController::class, 'forceDelete'])->name('posts.force-delete');

            // Resource Routes after custom routes
            Route::resource('posts',      PostController::class);
            Route::resource('categories', CategoryController::class);
            Route::resource('tags',       TagController::class);
        });

        Route::middleware(['permission:delete comments'])->group(function () {
            Route::get('comments',                     [CommentController::class, 'index'])->name('comments.index');
            Route::patch('comments/{comment}/approve', [CommentController::class, 'approve'])->name('comments.approve');
            Route::delete('comments/{comment}',        [CommentController::class, 'destroy'])->name('comments.destroy');
        });

        Route::middleware(['role:admin'])->group(function () {
            Route::resource('users',       UserController::class)->except(['create', 'store']);
            Route::patch('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
            Route::resource('roles',       RoleController::class);
            Route::resource('permissions', PermissionController::class);
            Route::get('users/{user}/roles', [UserRoleController::class, 'edit'])->name('users.roles.edit');
            Route::put('users/{user}/roles', [UserRoleController::class, 'update'])->name('users.roles.update');
        });

    });
