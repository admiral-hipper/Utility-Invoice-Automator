<?php

namespace Tests\Feature\Policies;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class InvoicePolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_view_own_invoice(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $customer = Customer::factory()->forUser($user)->create();

        $invoice = Invoice::factory()->create(['customer_id' => $customer->id]);

        $this->assertTrue(Gate::forUser($user)->allows('view', $invoice));
    }

    public function test_customer_cannot_view_foreign_invoice(): void
    {
        $userA = User::factory()->create(['role' => 'customer']);
        $customerA = Customer::factory()->forUser($userA)->create();

        $userB = User::factory()->create(['role' => 'customer']);
        $customerB = Customer::factory()->forUser($userB)->create();

        $invoiceB = Invoice::factory()->create(['customer_id' => $customerB->id]);

        $this->assertFalse(Gate::forUser($userA)->allows('view', $invoiceB));
    }
}
