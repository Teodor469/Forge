<?php

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
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->enum('type', ['savings', 'checking', 'credit_card', 'debit_card', 'investment', 'cash']);
            $table->decimal('balance', 12, 2)->default(0);
            $table->enum('currency', ['USD', 'EUR', 'GBP', 'JPY', 'CHF', 'CAD', 'AUD'])->default('EUR');
            $table->string('institution');
            $table->string('last_four_digits', 4)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
};
