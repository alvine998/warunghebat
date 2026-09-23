<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('store_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
            $table->unsignedInteger('subtotal')->default(0)->after('total');
            $table->unsignedInteger('commission_amount')->default(0)->after('subtotal');
            $table->timestamp('completed_at')->nullable()->after('status');

            // Admin and seller order lists filter by status, newest first.
            $table->index(['status', 'created_at']);
        });

        // 'Selesai' was a placeholder; orders now move through Order::STATUSES.
        Schema::table('orders', function (Blueprint $table) {
            $table->string('status', 20)->default('pending_payment')->change();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('status', 20)->default('Selesai')->change();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['status', 'created_at']);
            $table->dropConstrainedForeignId('store_id');
            $table->dropColumn(['subtotal', 'commission_amount', 'completed_at']);
        });
    }
};
