<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();

            // Sellers may delete a product; the order keeps its snapshot.
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();

            $table->string('name', 120);
            $table->unsignedInteger('price');
            $table->unsignedInteger('qty');
            $table->unsignedInteger('subtotal');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
