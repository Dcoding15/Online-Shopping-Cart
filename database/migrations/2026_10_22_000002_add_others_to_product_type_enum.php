<?php

use App\Models\Product;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Add the missing 'Others' value to the product_type check constraint in PostgreSQL.
     */
    public function up(): void
    {
        // In PostgreSQL, Laravel enum columns use a CHECK constraint named {table}_{column}_check
        DB::statement('ALTER TABLE products DROP CONSTRAINT IF EXISTS products_product_type_check');

        $types = Product::TYPES;
        $quotedTypes = array_map(function ($type) {
            return "'" . str_replace("'", "''", $type) . "'::character varying";
        }, $types);

        $arrayDef = 'ARRAY[' . implode(', ', $quotedTypes) . ']::text[]';

        DB::statement("ALTER TABLE products ADD CONSTRAINT products_product_type_check CHECK ((product_type IS NULL) OR ((product_type)::text = ANY ({$arrayDef})))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE products DROP CONSTRAINT IF EXISTS products_product_type_check');

        $types = array_filter(Product::TYPES, fn ($t) => $t !== 'Others');
        $quotedTypes = array_map(function ($type) {
            return "'" . str_replace("'", "''", $type) . "'::character varying";
        }, $types);

        $arrayDef = 'ARRAY[' . implode(', ', $quotedTypes) . ']::text[]';

        DB::statement("ALTER TABLE products ADD CONSTRAINT products_product_type_check CHECK ((product_type IS NULL) OR ((product_type)::text = ANY ({$arrayDef})))");
    }
};
