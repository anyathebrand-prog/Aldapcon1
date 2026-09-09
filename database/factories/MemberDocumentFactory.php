<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Membership\Models\Applicant;
use App\Domain\Membership\Models\MemberDocument;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<MemberDocument> */
final class MemberDocumentFactory extends Factory
{
    protected $model = MemberDocument::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $uuid = (string) Str::uuid();
        $applicantUuid = (string) Str::uuid();

        return [
            'uuid' => $uuid,
            'applicant_id' => Applicant::factory(),
            'type' => 'ndpc_certificate',
            'disk' => 'private',
            // Never under public/, never symlinked (Schema §6.1).
            'path' => "certificates/{$applicantUuid}/{$uuid}.pdf",
            'original_filename' => 'ndpc-licence.pdf',
            'mime_type' => 'application/pdf',
            'size_bytes' => 250_000,
            'sha256' => hash('sha256', $uuid),
            'uploaded_at' => now(),
        ];
    }
}
