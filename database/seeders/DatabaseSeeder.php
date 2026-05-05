<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Execution order respects foreign-key dependencies:
     *   Admin → Lookup tables → Provider stack → Service stack
     *   → Shift stack → Pricing stack → Users → Bookings
     *   → Payments → Attendance → Ratings
     */
    public function run(): void
    {
        // ── Core accounts ──────────────────────────────────────────
        $this->call(AdminSeeder::class);
        $this->call(UserSeeder::class);

        // ── Lookup / geography ─────────────────────────────────────
        $this->call(CountriesSeeder::class);
        $this->call(GovernoratesSeeder::class);
        $this->call(CitiesSeeder::class);
        $this->call(CitiesByGovernorateSeeder::class);

        // ── Category taxonomy ──────────────────────────────────────
        $this->call(CategorySeeder::class);
        $this->call(SubcategorySeeder::class);

        // ── Provider stack ─────────────────────────────────────────
        $this->call(ProviderSeeder::class);
        $this->call(ProviderBranchSeeder::class);
        $this->call(EmployeeSeeder::class);

        // ── Services ───────────────────────────────────────────────
        $this->call(ServiceSeeder::class);
        $this->call(ProviderServiceSeeder::class);

        // ── Shifts ─────────────────────────────────────────────────
        $this->call(ShiftTemplateSeeder::class);
        $this->call(ShiftSeeder::class);
        $this->call(ShiftDateSeeder::class);
        $this->call(ShiftDateEmployeeSeeder::class);

        // ── Pricing ────────────────────────────────────────────────
        $this->call(PricingGroupSeeder::class);
        $this->call(PricingGroupEmployeeSeeder::class);
        $this->call(ServicePriceSeeder::class);

        // ── Transactional data ─────────────────────────────────────
        $this->call(BookingSeeder::class);
        $this->call(PaymentSeeder::class);
        $this->call(AttendanceSeeder::class);
        $this->call(RatingSeeder::class);

        // ── Content pages ──────────────────────────────────────────
        $this->call(ContentPageSeeder::class);

        // ── Announcements ──────────────────────────────────────────
        $this->call(AnnouncementSeeder::class);

        // ── Banners ────────────────────────────────────────────────
        $this->call(BannerSeeder::class);

        // ── Promo Codes ────────────────────────────────────────────
        $this->call(PromoCodeSeeder::class);
    }
}

