<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Roles and permissions — Schema §2.2, FR-9.7, AC-F9.
 *
 * Four roles. The grant lists below ARE the access control policy; the
 * policies and middleware in Phase 4 enforce what is declared here.
 *
 * The rule that matters most:
 *
 *   A PUBLISHER MUST NOT REACH MEMBER, PAYMENT OR CERTIFICATE DATA BY ANY
 *   ROUTE, INCLUDING A DIRECT URL (AC-F9, Schema §5.3 rule 2).
 *
 * That is achieved by their permission set containing NO grant that touches
 * those tables — not by hiding menu items. Hiding a menu item is not
 * authorisation (plan §2 rule 6).
 */
final class RoleSeeder extends Seeder
{
    /**
     * Every permission in the system — Schema §2.2, verb.resource.
     *
     * @var list<string>
     */
    public const PERMISSIONS = [
        'members.view', 'members.update', 'members.deactivate',
        'members.delete', 'members.export',

        'payments.view', 'payments.export', 'payments.reverify',
        'refunds.record',

        'applicants.view', 'applicants.resend',

        'categories.manage',

        'events.manage', 'events.attendees.view',

        'content.manage',            // posts, pages, leadership, FAQ
        'announcements.manage',

        'enquiries.view', 'subscribers.view',
        'data_requests.manage',

        'users.manage', 'audit.view', 'settings.manage',

        // Change set 01 — certificate verification. None of these reaches
        // Publisher, and D-24 (the certificate stream) checks documents.view.
        'verifications.view', 'verifications.decide', 'documents.view',
    ];

    /**
     * Admin has everything except the four Super Admin reserves.
     *
     * members.delete is Super Admin only because deletion interacts with
     * financial retention (Schema §6.3) — a member with payments is
     * anonymised, never deleted.
     *
     * audit.view is Super Admin only because "a log an actor can read is a log
     * they can plan around" (Schema §5.3 rule 3).
     *
     * @var list<string>
     */
    private const ADMIN_EXCLUDES = [
        'users.manage',
        'audit.view',
        'settings.manage',
        'members.delete',
    ];

    /**
     * Content and events only. Events are content for this purpose, which
     * resolves plan C-6: FR-9.7 says Publisher is "content only" while App
     * Flow D-07/D-08 grants event management.
     *
     * @var list<string>
     */
    private const PUBLISHER_GRANTS = [
        'content.manage',
        'events.manage',
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (self::PERMISSIONS as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $superAdmin = Role::findOrCreate('super_admin', 'web');
        $superAdmin->syncPermissions(self::PERMISSIONS);

        $admin = Role::findOrCreate('admin', 'web');
        $admin->syncPermissions(
            array_values(array_diff(self::PERMISSIONS, self::ADMIN_EXCLUDES))
        );

        $publisher = Role::findOrCreate('publisher', 'web');
        $publisher->syncPermissions(self::PUBLISHER_GRANTS);

        // Member holds NO administrative permission at all (FR-9.7, audit
        // IG-12). Portal access is granted by record ownership, not by
        // permission — a member reaches their own rows through the
        // authenticated user, never through a parameter (Schema §5.1).
        $member = Role::findOrCreate('member', 'web');
        $member->syncPermissions([]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
