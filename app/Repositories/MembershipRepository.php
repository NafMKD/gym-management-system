<?php

namespace App\Repositories;

use App\Models\Membership;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Label\Label;
use Endroid\QrCode\Logo\Logo;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\RoundBlockSizeMode;

class MembershipRepository extends BaseRepository {

    /**
     * Store a new membership in the database.
     *
     * @param array $attributes
     * @return bool
     */
    public function store(array $attributes): mixed
    {
        try {
            return DB::transaction(function () use ($attributes) {
                $validatedAttributes = [
                    'user_id' => $attributes['user_id'] ?? null,
                    'start_date' => $attributes['start_date'] ?? null,
                    'end_date' => $attributes['end_date'] ?? null,
                    'package_id' => $attributes['package_id'] ?? null,
                    'remaining_days' => $attributes['remaining_days'] ?? null,
                    'status' => $attributes['status'] ?? 'inactive',
                    'price' => $attributes['price'] ?? null,
                ];

                
                if (!isset($validatedAttributes['user_id'], $validatedAttributes['start_date'], $validatedAttributes['end_date'], $validatedAttributes['remaining_days'], $validatedAttributes['status'], $validatedAttributes['price'])) {
                    throw new \Exception("Missing required attributes.");
                }

                $user = User::find($validatedAttributes['user_id']);

                if ($user->memberships()->where('status', 'active')->exists()) {
                    throw new \Exception("User already has an active membership.");
                }

                $membership = Membership::create($validatedAttributes);

                // Generate QR Code
                $membershipId = $membership->id;
                $timestamp = now()->format('Y_m_d_H_i_s_u');
                $fileName = "qr_code_{$timestamp}.png";

                $writer = new PngWriter();

                // Create QR code
                $qrCode = new QrCode(
                    data: bcrypt($membershipId), // Use encrypted membership ID
                    encoding: new Encoding('UTF-8'),
                    errorCorrectionLevel: ErrorCorrectionLevel::Low,
                    size: 300,
                    margin: 10,
                    roundBlockSizeMode: RoundBlockSizeMode::Margin,
                    foregroundColor: new Color(0, 0, 0), // Black foreground
                    backgroundColor: new Color(255, 255, 255) // White background
                );

                // Optional: Add logo (if applicable)
                $logoPath = public_path('assets/dist/img/logo.jpg'); // Replace with your logo path
                $logo = file_exists($logoPath)
                    ? new Logo(
                        path: $logoPath,
                        resizeToWidth: 50,
                        punchoutBackground: true
                    )
                    : null;

                // Optional: Add label
                $label = new Label(
                    text: 'Membership QR',
                    textColor: new Color(255, 0, 0) // Red text color
                );

                $result = $writer->write($qrCode, $logo, $label);

                // Save the QR code file
                $filePath = public_path("qr_codes/{$fileName}");

                if (!file_exists(public_path('qr_codes'))) {
                    mkdir(public_path('qr_codes'), 0777, true); // Create the directory if it doesn't exist
                }

                file_put_contents($filePath, $result->getString());

                $membership->update(['qr_code' => $fileName]);
                return $membership;
            });

        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Update an existing membership in the database.
     *
     * @param mixed $model
     * @param array $attributes
     * @return mixed
     */
    public function update(mixed $model, array $attributes): mixed
    {
        return null;
    }
}
