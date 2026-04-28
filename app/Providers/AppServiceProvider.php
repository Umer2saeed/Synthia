<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Observers\CategoryObserver;
use App\Observers\PostObserver;
use App\Observers\TagObserver;
use App\Services\ImageOptimizationService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Input Sanitization Service
        $this->app->singleton(\App\Services\SanitizationService::class);
        // CacheService
        $this->app->singleton(\App\Services\CacheService::class);

        $this->app->singleton(ImageManager::class, function () {
            return new ImageManager(new GdDriver());
        });

        // Register our services as singletons
        $this->app->singleton(ImageOptimizationService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::preventLazyLoading(!app()->isProduction());

        // Paginator
        Paginator::useTailwind();
        Paginator::defaultView('vendor.pagination.tailwind');
        Paginator::defaultSimpleView('vendor.pagination.simple-tailwind');


       // Register Email Layout as a Blade Component
        Blade::anonymousComponentPath(resource_path('views/emails'), 'emails');

        // Observers
        Post::observe(PostObserver::class);
        Category::observe(CategoryObserver::class);
        Tag::observe(TagObserver::class);

        // Queues
        Queue::before(function (JobProcessing $event) {
            Log::info('Queue: job starting', [
                'job'        => $event->job->resolveName(),
                'queue'      => $event->job->getQueue(),
                'attempt'    => $event->job->attempts(),
            ]);
        });

        Queue::after(function (JobProcessed $event) {
            Log::info('Queue: job completed', [
                'job'   => $event->job->resolveName(),
                'queue' => $event->job->getQueue(),
            ]);
        });

        Queue::failing(function (JobFailed $event) {
            Log::error('Queue: job failed permanently', [
                'job'       => $event->job->resolveName(),
                'queue'     => $event->job->getQueue(),
                'exception' => $event->exception->getMessage(),
            ]);
        });

    }
}
