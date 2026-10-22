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
        Schema::create('products', function (Blueprint $table) {
            $table->bigIncrements('product_id');
            $table->string('product_name', 200);
            $table->unsignedBigInteger('user_id');
            $table->integer('product_available_quantity')->default(0);
            $table->decimal('product_price', 12, 2);
            $table->binary('product_picture')->nullable();
            $table->enum('product_type', ['Pet Food', 'Pet Toys', 'Aquarium Filters', 'Bird Cages', 'Dog Leashes', 'Clothing', 'Shoes', 'Jewellery', 'Sunglasses', 'Handbags', 'Musical Instruments', 'Paintbrushes', 'Party Supplies', 'Sheet Music', 'Craft Foam', 'Diapers', 'Baby Strollers', 'Pacifiers', 'Baby Bottles', 'Car Seats', 'Lab Flasks', 'Safety Goggles', 'Medical Scrubs', 'Heavy Machinery', 'Retail Racks', 'Camera Lenses', 'Binoculars', 'Telescopes', 'Tripods', 'Digital Cameras', 'Smartphones', 'Laptops', 'Headphones', 'Televisions', 'Smartwatches', 'Coffee Beans', 'Cheesecake', 'Green Tea', 'Olive Oil', 'Spices', 'Sofas', 'Dining Tables', 'Office Chairs', 'Bed Frames', 'Bookshelves', 'Screws', 'Hammers', 'Power Drills', 'Door Knobs', 'Brackets', 'Lipstick', 'Moisturiser', 'Vitamins', 'Hair Dryers', 'Perfume', 'Bed Sheets', 'Lawn Mowers', 'Kitchen Knives', 'Indoor Plants', 'Wall Art', 'Suitcases', 'Backpacks', 'Wallets', 'Briefcases', 'Duffel Bags', 'Erotic Clothing', 'Adult Toys', 'Novelty Items', 'Books', 'DVDs', 'Vinyl Records', 'Magazines', 'Video Games', 'Printer Paper', 'Ballpoint Pens', 'Staplers', 'Whiteboards', 'File Folders', 'Altar Candles', 'Prayer Rugs', 'Religious Statues', 'Incense Holders', 'Ceremonial Robes', 'Antivirus Software', 'Video Editors', 'Operating Systems', 'Accounting Software', 'Tax Software', 'Yoga Mats', 'Football Sacks', 'Tennis Rackets', 'Bicycles', 'Dumbbells', 'Board Games', 'Puzzles', 'Action Figures', 'Dolls', 'Building Blocks', 'Car Batteries', 'Tires', 'Motorcycle Helmets', 'Brake Pads'])->nullable();
            $table->text('product_description')->nullable();
            $table->enum('product_category', ['Animals & Pet Supplies', 'Apparel & Accessories', 'Arts & Entertainment', 'Baby & Toddler', 'Business & Industrial', 'Cameras & Optics', 'Electronics', 'Food, Beverages & Tobacco', 'Furniture', 'Hardware', 'Health & Beauty', 'Home & Garden', 'Luggage & Bags', 'Mature', 'Media', 'Office Supplies', 'Religious & Ceremonial', 'Software', 'Sporting Goods', 'Toys & Games', 'Vehicles & Parts']);
            $table->jsonb('product_meta_data')->nullable();
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
