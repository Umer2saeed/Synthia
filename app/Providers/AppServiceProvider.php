<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator; // Import this
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useTailwind();

        /*
        |----------------------------------------------------------------------
        | Set Tailwind CSS as the default pagination view
        |----------------------------------------------------------------------
        | By default Laravel uses Bootstrap pagination. This switches it to
        | the Tailwind view we published above so all paginator calls across
        | the entire app — admin panel and frontend — use the same styling.
        |
        | This means every ->paginate() call automatically uses this view
        | without needing to call ->links('vendor.pagination.tailwind')
        | manually on each page.
        */
        Paginator::defaultView('vendor.pagination.tailwind');
        Paginator::defaultSimpleView('vendor.pagination.simple-tailwind');
    }
}
