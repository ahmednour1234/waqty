# Development Guide

## Creating Models

All domain models must extend `BaseModel`:

```php
<?php

namespace App\Models;

class Booking extends BaseModel
{
    protected $fillable = ['title', 'date', 'status'];
    
    // Automatically scoped to user_id
    // All queries filtered by authenticated user
}
```

## Creating Controllers

All controllers must extend `BaseController`:

```php
<?php

namespace App\Http\Controllers;

use App\Models\Booking;

class BookingController extends BaseController
{
    public function index()
    {
        // Automatically scoped to current user
        return Booking::all();
    }

    public function show(Booking $booking)
    {
        // Authorization checked via policy
        // User ownership validated
        return $booking;
    }

    public function store(Request $request)
    {
        // user_id automatically set
        return Booking::create($request->validated());
    }
}
```

## Creating Policies

All policies must extend `BasePolicy`:

```php
<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;

class BookingPolicy extends BasePolicy
{
    // Inherits viewAny, view, create, update, delete
    // Automatically validates user ownership
    
    // Override for custom logic:
    public function update(User $user, Booking $booking): bool
    {
        // Custom logic here
        return parent::update($user, $booking);
    }
}
```

## Creating Migrations

Migrations should use `RequiresIndexes` trait:

```php
<?php

use App\Database\Migrations\Concerns\RequiresIndexes;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    use RequiresIndexes;

    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->index();
            $table->string('title');
            $table->date('date');
            $table->timestamps();
            
            // Index frequently queried columns
            $table->index('date');
            $table->index(['user_id', 'date']); // Composite index
        });
        
        // Validate indexes
        $this->validateIndexes('bookings');
    }
};
```

## Creating Jobs

All jobs must extend `BaseJob`:

```php
<?php

namespace App\Jobs;

use App\Models\Booking;

class ProcessBooking extends BaseJob
{
    public function __construct(
        public Booking $booking
    ) {}

    protected function execute(): void
    {
        // Automatically wrapped in transaction
        // Automatic retry on deadlocks
        $this->booking->update(['status' => 'processed']);
    }
}
```

## Creating Form Requests

All form requests must extend `BaseRequest`:

```php
<?php

namespace App\Http\Requests;

class StoreBookingRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date'],
        ];
    }
    
    // Authorization and user context handled automatically
}
```

## Using Transactions

Use `TransactionalOperations` trait for transaction management:

```php
<?php

namespace App\Services;

use App\Database\Concerns\TransactionalOperations;

class BookingService
{
    use TransactionalOperations;

    public function createBooking(array $data)
    {
        return $this->executeInTransaction(function () use ($data) {
            $booking = Booking::create($data);
            // Related operations
            return $booking;
        });
    }
    
    public function updateBookingWithLock(Booking $booking, array $data)
    {
        return $this->withLock('bookings', $booking->id, function ($model) use ($data) {
            // Pessimistic lock acquired
            return Booking::where('id', $booking->id)->update($data);
        });
    }
}
```

## Using Locks

Use `LockManager` for distributed locking:

```php
use App\Database\LockManager;

$lockManager = new LockManager();

$lockManager->withLock('booking:123', 10, function () {
    // Critical section with distributed lock
});
```

## Performance Testing

Use `AssertQueryPerformance` in tests:

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use Tests\Concerns\AssertQueryPerformance;
use App\Models\Booking;

class BookingTest extends TestCase
{
    use AssertQueryPerformance;

    public function test_index_performance()
    {
        $this->assertMaxQueries(5, function () {
            Booking::with('user')->get();
        });
        
        $this->assertNoNPlusOne(function () {
            Booking::with('user')->get();
        });
    }
}
```

## Common Patterns

### Eager Loading

Always eager load relationships to avoid N+1:

```php
// Good
Booking::with('user')->get();

// Bad - causes N+1
Booking::all()->each(fn($b) => $b->user);
```

### Query Scoping

User scoping is automatic, but you can bypass if needed:

```php
// Normal query (automatically scoped)
Booking::all();

// Bypass scope (use with caution)
Booking::withoutUserScope()->get();
```

### Authorization Checks

Authorization is automatic, but you can check manually:

```php
// In controller
$this->authorizeAction('update', $booking);

// In policy
Gate::allows('update', $booking);
```
