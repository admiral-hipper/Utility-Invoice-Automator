<?php

namespace Tests\Feature\Policies;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class CustomerPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_any_customers(): void
    {
        $admin = User::factory()->admin()->create();

        $allowed = Gate::forUser($admin)->allows('viewAny', Customer::class);

        $this->assertTrue($allowed);
    }

    public function test_customer_cannot_view_any_customers(): void
    {
        $user = User::factory()->create(['role' => 'customer']);

        $allowed = Gate::forUser($user)->allows('viewAny', Customer::class);

        $this->assertFalse($allowed);
    }
}
