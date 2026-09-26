<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->after('category')->constrained()->nullOnDelete();
            $table->foreignId('brand_id')->nullable()->after('category_id')->constrained()->nullOnDelete();
            $table->index(['category_id', 'status']);
            $table->index(['brand_id', 'status']);
        });

        // Backfill categories from the legacy hardcoded list, then link products.
        $defaults = [
            ['name' => 'Makanan', 'icon' => 'utensils', 'description' => 'Siap saji & masakan', 'sort_order' => 1],
            ['name' => 'Minuman', 'icon' => 'cup', 'description' => 'Kopi, jus & es', 'sort_order' => 2],
            ['name' => 'Sembako', 'icon' => 'basket', 'description' => 'Beras, telur, minyak', 'sort_order' => 3],
            ['name' => 'Harian', 'icon' => 'package', 'description' => 'Sabun & tisu', 'sort_order' => 4],
            ['name' => 'Jajanan', 'icon' => 'cookie', 'description' => 'Pasar & kekinian', 'sort_order' => 5],
            ['name' => 'Frozen', 'icon' => 'snowflake', 'description' => 'Nugget & dimsum', 'sort_order' => 6],
            ['name' => 'Lainnya', 'icon' => 'package', 'description' => 'Lain-lain', 'sort_order' => 99],
        ];

        foreach ($defaults as $row) {
            DB::table('categories')->updateOrInsert(
                ['slug' => Str::slug($row['name'])],
                [
                    'name' => $row['name'],
                    'slug' => Str::slug($row['name']),
                    'icon' => $row['icon'],
                    'description' => $row['description'],
                    'is_active' => true,
                    'sort_order' => $row['sort_order'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        $map = DB::table('categories')->pluck('id', 'name');

        foreach ($map as $name => $id) {
            DB::table('products')->where('category', $name)->whereNull('category_id')->update(['category_id' => $id]);
        }

        // Products with an unknown legacy string get folded into "Lainnya".
        $fallback = $map['Lainnya'] ?? null;

        if ($fallback !== null) {
            DB::table('products')->whereNull('category_id')->update(['category_id' => $fallback]);
        }
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('brand_id');
            $table->dropConstrainedForeignId('category_id');
        });
    }
};
