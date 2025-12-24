<?php

use App\Enums\InvoiceStatus;
use App\Models\Customer;
use App\Models\Import;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_no')->unique();
            $table->string('payment_ref')->unique();
            $table->foreignIdFor(Customer::class, 'customer_id');
            $table->foreignIdFor(Import::class, 'import_id');
            $table->string('period');
            $table->string('currency', 3)->default('RON')->index(); // RON/EUR
            $table->decimal('total', 12, 2)->default(0);
            $table->date('due_date')->nullable()->index();
            $table->timestamp('issued_at')->nullable()->index();
            $table->enum('status', InvoiceStatus::cases());
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
