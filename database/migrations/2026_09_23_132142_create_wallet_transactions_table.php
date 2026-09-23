<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained()->cascadeOnDelete();
            $table->string('type', 20);
            $table->unsignedInteger('amount');
            $table->unsignedInteger('balance_after');
            $table->string('description', 255);
            $table->nullableMorphs('reference');
            $table->timestamps();

            // One credit per order (and one debit per withdrawal) even under a double submit.
            $table->unique(
                ['wallet_id', 'reference_type', 'reference_id', 'type'],
                'wallet_transactions_ref_unique',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};
