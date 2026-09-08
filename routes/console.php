<?php

declare(strict_types=1);

use App\Console\Commands\Heartbeat;
use Illuminate\Support\Facades\Schedule;

/*
 * The task scheduler.
 *
 * Phase 11 adds TransitionMembershipStatuses and SendRenewalReminders here.
 * The heartbeat runs from Phase 1 so that "is the scheduler alive?" has an
 * answer before anything depends on the answer being yes.
 */
Schedule::command(Heartbeat::class)
    ->everyMinute()
    ->withoutOverlapping();
