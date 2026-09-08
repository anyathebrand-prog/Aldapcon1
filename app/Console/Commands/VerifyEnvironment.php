<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use RuntimeException;
use Throwable;

/**
 * Phase 1 verification, runnable by a human.
 *
 * The plan's Phase 1 completion criteria are four checks plus a working clone.
 * The automated tests in tests/Feature/Infrastructure prove them in CI; this
 * command proves them on a developer's machine, where the failure modes are
 * different — a wrong .env, a container that did not start, a Redis bound to
 * the wrong host.
 *
 * It is deliberately read-only. It asserts, it does not repair.
 */
final class VerifyEnvironment extends Command
{
    protected $signature = 'aldapcon:verify-environment';

    protected $description = 'Check that PostgreSQL, Redis, the queue and the scheduler are wired up correctly';

    public function handle(): int
    {
        $this->info('Verifying the ALDAPCON local environment');
        $this->newLine();

        $failures = 0;

        $failures += $this->check(
            'PostgreSQL reachable and version 16 or above',
            function (): string {
                /** @var object{server_version: string} $row */
                $row = DB::selectOne('SHOW server_version');
                $version = $row->server_version;

                if (version_compare($version, '16', '<')) {
                    throw new RuntimeException("PostgreSQL {$version} found, 16+ required (TRD §2.2)");
                }

                return "PostgreSQL {$version}";
            }
        );

        $failures += $this->check(
            'Database driver is pgsql',
            function (): string {
                $driver = DB::connection()->getDriverName();

                if ($driver !== 'pgsql') {
                    throw new RuntimeException("Driver is '{$driver}', expected 'pgsql' (TRD §2.2)");
                }

                return $driver;
            }
        );

        $failures += $this->check(
            'Redis reachable',
            function (): string {
                Redis::connection()->ping();

                return 'PONG';
            }
        );

        $failures += $this->check(
            'Cache store is Redis',
            fn (): string => $this->assertConfig('cache.default', 'redis', 'TRD §2.3')
        );

        $failures += $this->check(
            'Session driver is Redis',
            // Schema §2.1 records that there is no sessions table precisely
            // because sessions live in Redis. Changing this driver quietly
            // invalidates that decision and App Flow M-11's deferral.
            fn (): string => $this->assertConfig('session.driver', 'redis', 'TRD §2.3, Schema §2.1')
        );

        $failures += $this->check(
            'Queue connection is Redis',
            fn (): string => $this->assertConfig('queue.default', 'redis', 'TRD §2.3')
        );

        $failures += $this->check(
            'Application timezone is UTC',
            // Schema §1.4: stored UTC, rendered Africa/Lagos. Storing local
            // time makes every expiry comparison in Phase 11 ambiguous.
            fn (): string => $this->assertConfig('app.timezone', 'UTC', 'Schema §1.4')
        );

        $failures += $this->check(
            'Application key is set',
            function (): string {
                if (config('app.key') === null || config('app.key') === '') {
                    throw new RuntimeException('APP_KEY is empty — run php artisan key:generate');
                }

                return 'set';
            }
        );

        $this->newLine();

        if ($failures > 0) {
            $this->error("{$failures} check(s) failed. The environment is not ready.");

            return self::FAILURE;
        }

        $this->info('All checks passed.');
        $this->line('  Queue worker and scheduler are proven by the test suite:');
        $this->line('  php artisan test --filter=Infrastructure');

        return self::SUCCESS;
    }

    /**
     * @param  callable(): string  $assertion
     */
    private function check(string $label, callable $assertion): int
    {
        try {
            $detail = $assertion();
            $this->line(sprintf('  <fg=green>PASS</> %s <fg=gray>(%s)</>', $label, $detail));

            return 0;
        } catch (Throwable $e) {
            $this->line(sprintf('  <fg=red>FAIL</> %s', $label));
            $this->line(sprintf('       <fg=red>%s</>', $e->getMessage()));

            return 1;
        }
    }

    private function assertConfig(string $key, string $expected, string $reference): string
    {
        $actual = config($key);

        if ($actual !== $expected) {
            throw new RuntimeException(
                sprintf("config('%s') is '%s', expected '%s' (%s)", $key, var_export($actual, true), $expected, $reference)
            );
        }

        return $expected;
    }
}
