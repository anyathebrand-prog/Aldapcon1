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

/*
 * Concurrency tests run WITHOUT RefreshDatabase, against committed data, and
 * clean up after themselves.
 *
 * They have to. RefreshDatabase wraps each test in a transaction that is
 * rolled back, which makes two things untestable: a guard that checks whether
 * a transaction is already open (it always is), and a second connection
 * contending for a lock (it cannot see uncommitted rows, so it finds no row to
 * contend for).
 *
 * In both cases the assertion would pass whether or not the behaviour was
 * present — which is the one thing a test must never do, and doubly so for
 * the membership number allocator, where a collision is unfixable after the
 * fact (Schema §8.4).
 */
pest()->extend(TestCase::class)
    ->in('Concurrency');
