<?php

use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Console Routes — Laravel 11+ scheduling
|--------------------------------------------------------------------------
| This file is where you define all scheduled tasks for your application.
| The Schedule facade lets you chain frequency methods to control when
| each command runs.
|
| The server only needs ONE cron entry:
|   * * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
|
| Laravel then manages all the timing internally from this file.
*/

Schedule::command('posts:publish-scheduled')->everyMinute()->withoutOverlapping()->runInBackground();


// Queue Monitor - Every 5 minutes
Schedule::command('queue:monitor-synthia')->everyFiveMinutes()->runInBackground()->appendOutputTo(storage_path('logs/queue-monitor.log'));
