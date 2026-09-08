<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Identity\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

/**
 * Creates the first Super Admin — Schema §8.2.
 *
 * Deliberately a console command rather than a seeder. A seeded admin has a
 * password that is in the repository, known to everybody who has ever read it,
 * and it survives into production as a backdoor that nobody remembers to
 * close. This asks for the password at the terminal and never writes it
 * anywhere.
 *
 * The account is created WITHOUT a second factor, which forces the first login
 * through A-07 enrolment (FR-4.3, plan C-4). That is the intended path: the
 * RequireTwoFactor middleware will not let them reach any admin route until
 * they have finished.
 *
 * Email is marked verified because whoever runs this command has server
 * access; asking them to click a link in an inbox that may not be configured
 * yet would block the very first login on Phase 7 being finished.
 */
final class CreateSuperAdmin extends Command
{
    protected $signature = 'aldapcon:create-super-admin
                            {--name= : Full name}
                            {--email= : Email address}';

    protected $description = 'Create the first Super Admin account';

    public function handle(): int
    {
        $name = (string) ($this->option('name') ?? $this->ask('Full name'));
        $email = (string) ($this->option('email') ?? $this->ask('Email address'));

        // secret() so it is not echoed and does not land in shell history.
        $password = (string) $this->secret('Password (minimum 12 characters)');
        $confirmation = (string) $this->secret('Confirm password');

        $validator = Validator::make(
            ['name' => $name, 'email' => $email, 'password' => $password],
            [
                'name' => ['required', 'string', 'max:180'],
                'email' => ['required', 'email', 'max:180', 'unique:users,email'],
                'password' => ['required', Password::min(12)->uncompromised()],
            ]
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        if ($password !== $confirmation) {
            $this->error('The passwords do not match.');

            return self::FAILURE;
        }

        $user = DB::transaction(function () use ($name, $email, $password): User {
            $user = User::query()->create([
                'full_name' => $name,
                'email' => $email,
                'phone' => '',
                'password' => Hash::make($password),
            ]);

            $user->forceFill([
                'uuid' => (string) Str::uuid(),
                'email_verified_at' => now(),
            ])->save();

            $user->assignRole('super_admin');

            return $user;
        });

        activity()
            ->performedOn($user)
            ->withProperties(['role' => 'super_admin', 'via' => 'console'])
            ->log('Super Admin account created');

        $this->newLine();
        $this->info("Super Admin created: {$user->email}");
        $this->line('  Two-factor authentication is NOT yet set up.');
        $this->line('  The first login will require it before any admin page opens (FR-4.3).');

        return self::SUCCESS;
    }
}
