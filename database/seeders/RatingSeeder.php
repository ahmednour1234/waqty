<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Provider;
use App\Models\Rating;
use App\Models\User;
use Illuminate\Database\Seeder;

class RatingSeeder extends Seeder
{
    public function run(): void
    {
        $provider = Provider::where('email', 'provider@example.com')->first();

        if (!$provider) {
            if ($this->command) {
                $this->command->warn('Provider not found. Please run ProviderSeeder first.');
            }
            return;
        }

        // Only completed bookings that have a user can be rated
        $completedBookings = Booking::where('provider_id', $provider->id)
            ->where('status', Booking::STATUS_COMPLETED)
            ->whereNotNull('user_id')
            ->get();

        if ($completedBookings->isEmpty()) {
            if ($this->command) {
                $this->command->warn('No completed bookings found. Please run BookingSeeder first.');
            }
            return;
        }

        $ratingsData = [
            ['rating' => 5, 'comment' => 'Excellent service, highly recommend!', 'active' => true],
            ['rating' => 4, 'comment' => 'Very good experience overall.', 'active' => true],
            ['rating' => 5, 'comment' => 'Professional and friendly staff.', 'active' => true],
        ];

        $count = 0;

        foreach ($completedBookings as $index => $booking) {
            $exists = Rating::where('booking_id', $booking->id)->exists();

            if ($exists) {
                continue;
            }

            $ratingData = $ratingsData[$index % count($ratingsData)];

            Rating::create([
                'booking_id' => $booking->id,
                'user_id'    => $booking->user_id,
                'rating'     => $ratingData['rating'],
                'comment'    => $ratingData['comment'],
                'active'     => $ratingData['active'],
            ]);

            $count++;
        }

        if ($this->command) {
            $this->command->info("Seeded {$count} ratings.");
        }
    }
}
