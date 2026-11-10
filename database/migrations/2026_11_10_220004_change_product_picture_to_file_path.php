<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Change product_picture from a bytea (binary) column to a varchar file-path column.
     * Any existing binary rows are cleared — their raw bytes cannot be recovered as valid paths.
     */
    public function up(): void
    {
        // Drop the old bytea column and replace with a nullable string (file path).
        DB::statement('ALTER TABLE products DROP COLUMN IF EXISTS product_picture');
        DB::statement('ALTER TABLE products ADD COLUMN product_picture VARCHAR(500) DEFAULT NULL');
    }

    /**
     * Restore to a bytea column on rollback.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE products DROP COLUMN IF EXISTS product_picture');
        DB::statement('ALTER TABLE products ADD COLUMN product_picture BYTEA DEFAULT NULL');
    }
};
