<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Employee;
use App\Models\Provider;
use App\Models\ProviderBranch;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BookingSeeder extends Seeder
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

        $branch = ProviderBranch::where('provider_id', $provider->id)
            ->where('is_main', true)
            ->first();

        $employee = Employee::where('email', 'employee@example.com')->first();

        $users = User::whereIn('email', [
            'ahmed@example.com',
            'sara@example.com',
            'omar@example.com',
            'fatima@example.com',
        ])->get()->keyBy('email');

        if ($users->isEmpty()) {
            if ($this->command) {
                $this->command->warn('Users not found. Please run UserSeeder first.');
            }
            return;
        }

        // Grab a few provider services
        $serviceIds = DB::table('provider_service')
            ->where('provider_id', $provider->id)
            ->where('active', true)
            ->limit(4)
            ->pluck('service_id')
            ->toArray();

        if (empty($serviceIds)) {
            if ($this->command) {
                $this->command->warn('No provider services found. Please run ProviderServiceSeeder first.');
            }
            return;
        }

        $services = Service::whereIn('id', $serviceIds)->get();

        $now    = Carbon::now();
        $past   = $now->copy()->subDays(10);
        $future = $now->copy()->addDays(3);

        $bookingDefinitions = [
            [
                'user'         => 'ahmed@example.com',
                'service_idx'  => 0,
                'date'         => $past->copy()->addDays(0)->toDateString(),
                'start_time'   => '10:00:00',
                'end_time'     => '10:30:00',
                'status'       => Booking::STATUS_COMPLETED,
                'payment_status' => Booking::PAYMENT_STATUS_PAID,
                'price'        => 150.00,
                'notes'        => 'First visit',
            ],
            [
                'user'         => 'sara@example.com',
                'service_idx'  => 1,
                'date'         => $past->copy()->addDays(2)->toDateString(),
                'start_time'   => '11:00:00',
                'end_time'     => '11:30:00',
                'status'       => Booking::STATUS_COMPLETED,
                'payment_status' => Booking::PAYMENT_STATUS_PAID,
                'price'        => 200.00,
                'notes'        => 'Regular customer',
            ],
            [
                'user'         => 'omar@example.com',
                'service_idx'  => 2,
                'date'         => $past->copy()->addDays(4)->toDateString(),
                'start_time'   => '14:00:00',
                'end_time'     => '14:30:00',
                'status'       => Booking::STATUS_CANCELLED,
                'payment_status' => Booking::PAYMENT_STATUS_UNPAID,
                'price'        => 120.00,
                'notes'        => 'Cancelled by customer',
                'cancellation_reason' => 'Changed plans',
                'cancelled_at' => $past->copy()->addDays(3),
            ],
            [
                'user'         => 'fatima@example.com',
                'service_idx'  => 0,
                'date'         => $past->copy()->addDays(6)->toDateString(),
                'start_time'   => '09:00:00',
                'end_time'     => '09:30:00',
                'status'       => Booking::STATUS_COMPLETED,
                'payment_status' => Booking::PAYMENT_STATUS_PAID,
                'price'        => 150.00,
                'notes'        => null,
            ],
            [
                'user'         => 'ahmed@example.com',
                'service_idx'  => 1,
                'date'         => $future->toDateString(),
                'start_time'   => '10:00:00',
                'end_time'     => '10:30:00',
                'status'       => Booking::STATUS_CONFIRMED,
                'payment_status' => Booking::PAYMENT_STATUS_UNPAID,
                'price'        => 200.00,
                'notes'        => 'Upcoming appointment',
            ],
            [
                'user'         => 'sara@example.com',
                'service_idx'  => 2,
                'date'         => $future->copy()->addDays(1)->toDateString(),
                'start_time'   => '13:00:00',
                'end_time'     => '13:30:00',
                'status'       => Booking::STATUS_PENDING,
                'payment_status' => Booking::PAYMENT_STATUS_UNPAID,
                'price'        => 120.00,
                'notes'        => null,
            ],
        ];

        $count = 0;

        foreach ($bookingDefinitions as $def) {
            $user    = $users->get($def['user']);
            $service = $services->values()->get($def['service_idx'] % $services->count());

            if (!$user || !$service) {
                continue;
            }

            // Check for an existing booking for this user/date/start_time
            $existing = Booking::where('provider_id', $provider->id)
                ->where('user_id', $user->id)
                ->where('booking_date', $def['date'])
                ->where('start_time', $def['start_time'])
                ->first();

            if ($existing) {
                continue;
            }

            $data = [
                'user_id'            => $user->id,
                'provider_id'        => $provider->id,
                'branch_id'          => $branch?->id,
                'employee_id'        => $employee?->id,
                'service_id'         => $service->id,
                'booking_date'       => $def['date'],
                'start_time'         => $def['start_time'],
                'end_time'           => $def['end_time'],
                'price'              => $def['price'],
                'currency'           => 'EGP',
                'status'             => $def['status'],
                'payment_status'     => $def['payment_status'],
                'notes'              => $def['notes'] ?? null,
                'cancellation_reason' => $def['cancellation_reason'] ?? null,
                'cancelled_at'       => $def['cancelled_at'] ?? null,
                'service_snapshot'   => ['name' => $service->name, 'id' => $service->id],
                'provider_snapshot'  => ['name' => $provider->name, 'id' => $provider->id],
                'branch_snapshot'    => $branch ? ['name' => $branch->name, 'id' => $branch->id] : null,
                'employee_snapshot'  => $employee ? ['name' => $employee->name, 'id' => $employee->id] : null,
            ];

            Booking::create($data);
            $count++;
        }

        if ($this->command) {
            $this->command->info("Seeded {$count} bookings.");
        }
    }
}
