<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\AutosaveController;
use App\Http\Controllers\Admin\BadgeController;
use App\Http\Controllers\Admin\BulkPostController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\Frontend\AuthorController;
use App\Http\Controllers\Frontend\BookmarkController;
use App\Http\Controllers\Frontend\ClapController;
use App\Http\Controllers\Frontend\CommentLikeController;
use App\Http\Controllers\Frontend\FollowController;
use App\Http\Controllers\Frontend\FrontendProfileController;
use App\Http\Controllers\Frontend\ReactionController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CommentController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\PostRevisionController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\TagController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\UserRoleController;
use App\Http\Controllers\Frontend\ActivityFeedController;
use App\Http\Controllers\Frontend\BlogController;
use App\Http\Controllers\Frontend\CategoryPageController;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\OgImageController;
use App\Http\Controllers\Frontend\ReaderDashboardController;
use App\Http\Controllers\Frontend\ReadingListController;
use App\Http\Controllers\Frontend\TagPageController;
use App\Http\Controllers\Frontend\SeriesController;
use App\Http\Controllers\Admin\SeriesController as AdminSeriesController;


/*
|--------------------------------------------------------------------------
| Public Frontend Routes
|--------------------------------------------------------------------------
*/
Route::get('/',                         [HomeController::class,         'index'])->name('home');
Route::get('/blog',                     [BlogController::class,         'index'])->name('blog');
Route::get('/blog/{slug}',              [BlogController::class,         'show'])->name('blog.post');
Route::get('/category/{slug}',          [CategoryPageController::class, 'show'])->name('blog.category');
Route::get('/tag/{slug}',               [TagPageController::class,      'show'])->name('blog.tag');
Route::get('/authors/{username}',  [AuthorController::class,       'show'])->name('author.profile');



/*
| Public Series Routes
*/
Route::get('/series',        [SeriesController::class, 'index'])->name('series.index');
Route::get('/series/{slug}', [SeriesController::class, 'show'])->name('series.show');
/*
|--------------------------------------------------------------------------
| RSS Feed Routes
|--------------------------------------------------------------------------
| These must be publicly accessible — no auth middleware.
| RSS readers are automated bots that do not have sessions.
*/
Route::get('/feed', [\App\Http\Controllers\FeedController::class, 'index'])->name('feed.index');
Route::get('/rss', [\App\Http\Controllers\FeedController::class, 'index'])->name('feed.rss'); // alias — /rss redirects to same feed
/*
| OG Image Route — public, no auth required.
| Search engines and social platforms fetch this URL directly.
| Place this alongside the RSS feed routes at the top of web.php.
*/
Route::get('/og-image/{post}', [OgImageController::class, 'show'])->name('og-image');

Route::get('/category/{slug}/feed', [\App\Http\Controllers\FeedController::class, 'category'])->name('feed.category');

Route::middleware(['auth', 'verified'])->group(function () {

    // Comments
    Route::post('/comments', [BlogController::class, 'storeComment'])->name('comments.store');
    Route::delete('/comments/{comment}', [BlogController::class, 'destroyComment'])->name('comments.destroy');

    // Claps
    Route::post('/posts/{post}/clap', [ClapController::class, 'clap'])->name('posts.clap');

    // Reactions
    Route::post('/posts/{post}/react', [ReactionController::class, 'toggle'])->middleware(['auth', 'verified'])->name('posts.react');

    // Bookmarks
    Route::post('/bookmarks',  [BookmarkController::class, 'toggle'])->name('bookmarks.toggle');
    Route::get('/bookmarks', [BookmarkController::class, 'index'])->name('bookmarks.index');

    // Follows
    Route::post('/authors/{author}/follow', [FollowController::class, 'toggle'])->name('authors.follow');
    Route::get('/following', [FollowController::class, 'following'])->name('following.index');
    Route::get('/followers', [FollowController::class, 'followers'])->name('followers.index');

   // Reactions
    Route::post('/posts/{post}/react', [ReactionController::class, 'toggle'])->middleware(['auth', 'verified'])->name('posts.react');
    // Activity Feed
    Route::get('/activity-feed', [ActivityFeedController::class, 'index',])->name('feed.activity');

    // Comment Likes
    Route::post('/comments/{comment}/like', [CommentLikeController::class, 'toggle'])->name('comments.like');
});

Route::middleware(['auth'])->name('frontend.')->group(function () {
    // Frontend profile routes
    Route::get('/profile',           [FrontendProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit',      [FrontendProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile',           [FrontendProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password',  [FrontendProfileController::class, 'updatePassword'])->name('profile.password');
    Route::delete('/profile/avatar', [FrontendProfileController::class, 'removeAvatar'])->name('profile.avatar.remove');

    Route::get('/dashboard', [ReaderDashboardController::class,'index'])->name('reader.dashboard');
});

/*
| Reading Lists
*/
Route::prefix('reading-lists')->name('reading-lists.')->group(function () {
    // Public route — anyone can view a public list
    Route::get('/{readingList}/{slug}', [ReadingListController::class, 'show'])->name('show');
});

Route::middleware(['auth', 'verified'])->prefix('reading-lists')->name('reading-lists.')->group(function () {

    Route::get('/',                                    [ReadingListController::class, 'index'])->name('index');
    Route::post('/',                                   [ReadingListController::class, 'store'])->name('store');
    Route::put('/{readingList}',                       [ReadingListController::class, 'update'])->name('update');
    Route::delete('/{readingList}',                    [ReadingListController::class, 'destroy'])->name('destroy');
    Route::post('/{readingList}/items',                [ReadingListController::class, 'toggleItem'])->name('items.toggle');
    Route::get('/user-lists',                          [ReadingListController::class, 'getUserLists'])->name('user-lists');

});

// Auth Routes (Breeze handles login/register/etc.)
require __DIR__.'/auth.php';

// No user can log out explicitly by putting the /logout in the URL
Route::get('/logout', fn() => redirect('/login'));

// Admin Panel Routes — must be authenticated
Route::middleware(['auth', 'verified', 'admin.access'])->prefix('admin')->name('admin.')->group(function () {

        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Admin Profile Routes — prefixed to avoid collision with frontend profile
        Route::get('/profile',    [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile',  [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
        Route::put('/password',   [ProfileController::class, 'updatePassword'])->name('profile.password');
        Route::delete('/avatar',  [ProfileController::class, 'removeAvatar'])->name('profile.avatar.remove');

        // Admin Panel Access Required
        Route::middleware(['permission:access admin panel'])->group(function () {

            // Posts — custom routes BEFORE resource to prevent conflicts
            Route::get('posts/trash',              [PostController::class, 'trash'])->name('posts.trash');
            Route::patch('posts/{id}/restore',     [PostController::class, 'restore'])->name('posts.restore');
            Route::delete('posts/{id}/force-delete', [PostController::class, 'forceDelete'])->name('posts.force-delete');


            // Post Revisions — nested under posts - these 3 routes are added before the post resource to avoid the conflict
            Route::get('posts/{post}/revisions',                        [PostRevisionController::class, 'index'])->name('posts.revisions.index');
            Route::get('posts/{post}/revisions/{revision}',             [PostRevisionController::class, 'show'])->name('posts.revisions.show');
            Route::post('posts/{post}/revisions/{revision}/restore',    [PostRevisionController::class, 'restore'])->name('posts.revisions.restore');
            // Resource Routes after custom routes
            Route::resource('posts',      PostController::class);

            // Categories and Tags
            Route::resource('categories', CategoryController::class);
            Route::resource('tags',       TagController::class);

            // Activity Log
            Route::get('/activity', [ActivityLogController::class, 'index'])->name('activity.index');
            Route::get('/activity', [ActivityLogController::class, 'index'])->name('activity.index');

            // Bulk Post Actions
            Route::post('/posts/bulk', [BulkPostController::class, 'apply'])->name('posts.bulk');
            Route::post('/posts/bulk', [BulkPostController::class, 'apply'])->name('posts.bulk');

            // Draft Autosave
            Route::post('/posts/autosave',  [AutosaveController::class, 'save'])->name('posts.autosave');
            Route::delete('/posts/autosave', [AutosaveController::class, 'discard'])->name('posts.autosave.discard');
            /*
             | POST not PUT — JavaScript fetch sends POST.
             | No /admin prefix — already applied by the group prefix('admin').
             */
            Route::post('/posts/autosave',   [AutosaveController::class, 'save'])->name('posts.autosave');
            Route::delete('/posts/autosave', [AutosaveController::class, 'discard'])->name('posts.autosave.discard');

            Route::resource('series', AdminSeriesController::class);

        });

        // Comment Management
        Route::middleware(['permission:delete comments'])->group(function () {
            Route::get('comments',                     [CommentController::class, 'index'])->name('comments.index');
            Route::patch('comments/{comment}/approve', [CommentController::class, 'approve'])->name('comments.approve');
            Route::delete('comments/{comment}',        [CommentController::class, 'destroy'])->name('comments.destroy');
        });

        // Admin-only Management (Users, Roles, Permissions)
        Route::middleware(['role:admin'])->group(function () {
            Route::resource('users',       UserController::class)->except(['create', 'store']);
            Route::patch('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
            Route::resource('roles',       RoleController::class);
            Route::resource('permissions', PermissionController::class);
            Route::get('users/{user}/roles', [UserRoleController::class, 'edit'])->name('users.roles.edit');
            Route::put('users/{user}/roles', [UserRoleController::class, 'update'])->name('users.roles.update');

            Route::get('badges',          [BadgeController::class, 'index'])->name('badges.index');
            Route::post('badges/award',   [BadgeController::class, 'award'])->name('badges.award');
            Route::post('badges/revoke',  [BadgeController::class, 'revoke'])->name('badges.revoke');
        });

    });
