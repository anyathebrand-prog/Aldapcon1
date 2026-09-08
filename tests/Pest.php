<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
 * Feature tests run against the real PostgreSQL and Redis services, never
 * against SQLite. The schema depends on CITEXT, partial unique indexes, JSONB,
 * generated TSVECTOR columns and num_nonnulls (05-backend-schema.md); a suite
 * that passed on another engine would prove nothing about production.
 *
 * RefreshDatabase is applied here rather than per file so no test can leave
 * rows behind for the next one. Redis is not transactional, so tests touching
 * the cache or the queue clean up their own keys explicitly.
 */
pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

pest()->extend(TestCase::class)
    ->in('Unit');
