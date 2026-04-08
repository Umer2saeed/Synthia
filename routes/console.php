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

Schedule::command('posts:publish-scheduled')
    /*
    |----------------------------------------------------------------------
    | ->everyMinute()
    |----------------------------------------------------------------------
    | Runs the command every minute.
    |
    | This is the right frequency for a publishing scheduler because:
    |   - An editor schedules a post for 09:00
    |   - At 09:00:00 the cron fires → command runs → post goes live
    |   - Maximum delay is 59 seconds — acceptable for a blog
    |
    | Alternatives you could use instead:
    |   ->everyFiveMinutes()   runs every 5 minutes
    |   ->hourly()             runs at the start of every hour
    |   ->dailyAt('08:00')     runs once per day at 8am
    |   ->weekdays()           chain to restrict to weekdays only
    |   ->between('8:00', '17:00') only runs between 8am and 5pm
    */
    ->everyMinute()

    /*
    |----------------------------------------------------------------------
    | ->withoutOverlapping()
    |----------------------------------------------------------------------
    | Prevents a second instance of the command from running if the
    | previous one is still executing.
    |
    | Example without this: if publish takes 90 seconds, a second command
    | starts at minute 2 while minute 1 is still running — causing
    | duplicate publishing attempts.
    |
    | With this: the second run is skipped if the first is still going.
    */
    ->withoutOverlapping()

    /*
    |----------------------------------------------------------------------
    | ->runInBackground()
    |----------------------------------------------------------------------
    | Runs the command in a background process so it does not block
    | other scheduled commands from starting on time.
    */
    ->runInBackground();
