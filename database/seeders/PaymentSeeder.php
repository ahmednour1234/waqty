<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Provider;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
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

        // Only seed payments for paid bookings
        $paidBookings = Booking::where('provider_id', $provider->id)
            ->where('payment_status', Booking::PAYMENT_STATUS_PAID)
            ->get();

        if ($paidBookings->isEmpty()) {
            if ($this->command) {
                $this->command->warn('No paid bookings found. Please run BookingSeeder first.');
            }
            return;
        }

        $count = 0;

        foreach ($paidBookings as $booking) {
            $exists = Payment::where('booking_id', $booking->id)->exists();

            if ($exists) {
                continue;
            }

            Payment::create([
                'booking_id'     => $booking->id,
                'payment_method' => Payment::METHOD_CASH,
                'amount'         => $booking->price,
                'status'         => Payment::STATUS_COMPLETED,
                'transaction_id' => null,
                'notes'          => 'Paid at counter',
            ]);

            $count++;
        }

        if ($this->command) {
            $this->command->info("Seeded {$count} payments.");
        }
    }
}
