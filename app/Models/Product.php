<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'products';

    protected $primaryKey = 'product_id';

    public $incrementing = true;

    protected $keyType = 'int';

    public const CATEGORIES = [
        'Animals & Pet Supplies',
        'Apparel & Accessories',
        'Arts & Entertainment',
        'Baby & Toddler',
        'Business & Industrial',
        'Cameras & Optics',
        'Electronics',
        'Food, Beverages & Tobacco',
        'Furniture',
        'Hardware',
        'Health & Beauty',
        'Home & Garden',
        'Luggage & Bags',
        'Mature',
        'Media',
        'Office Supplies',
        'Religious & Ceremonial',
        'Software',
        'Sporting Goods',
        'Toys & Games',
        'Vehicles & Parts',
        'Others',
    ];

    public const TYPES = [
        'Pet Food',
        'Pet Toys',
        'Aquarium Filters',
        'Bird Cages',
        'Dog Leashes',

        'Clothing',
        'Shoes',
        'Jewellery',
        'Sunglasses',
        'Handbags',

        'Musical Instruments',
        'Paintbrushes',
        'Party Supplies',
        'Sheet Music',
        'Craft Foam',

        'Diapers',
        'Baby Strollers',
        'Pacifiers',
        'Baby Bottles',
        'Car Seats',

        'Lab Flasks',
        'Safety Goggles',
        'Medical Scrubs',
        'Heavy Machinery',
        'Retail Racks',

        'Camera Lenses',
        'Binoculars',
        'Telescopes',
        'Tripods',
        'Digital Cameras',

        'Smartphones',
        'Laptops',
        'Headphones',
        'Televisions',
        'Smartwatches',

        'Coffee Beans',
        'Cheesecake',
        'Green Tea',
        'Olive Oil',
        'Spices',

        'Sofas',
        'Dining Tables',
        'Office Chairs',
        'Bed Frames',
        'Bookshelves',

        'Screws',
        'Hammers',
        'Power Drills',
        'Door Knobs',
        'Brackets',

        'Lipstick',
        'Moisturiser',
        'Vitamins',
        'Hair Dryers',
        'Perfume',

        'Bed Sheets',
        'Lawn Mowers',
        'Kitchen Knives',
        'Indoor Plants',
        'Wall Art',

        'Suitcases',
        'Backpacks',
        'Wallets',
        'Briefcases',
        'Duffel Bags',

        'Erotic Clothing',
        'Adult Toys',
        'Novelty Items',

        'Books',
        'DVDs',
        'Vinyl Records',
        'Magazines',
        'Video Games',

        'Printer Paper',
        'Ballpoint Pens',
        'Staplers',
        'Whiteboards',
        'File Folders',

        'Altar Candles',
        'Prayer Rugs',
        'Religious Statues',
        'Incense Holders',
        'Ceremonial Robes',

        'Antivirus Software',
        'Video Editors',
        'Operating Systems',
        'Accounting Software',
        'Tax Software',

        'Yoga Mats',
        'Football Sacks',
        'Tennis Rackets',
        'Bicycles',
        'Dumbbells',

        'Board Games',
        'Puzzles',
        'Action Figures',
        'Dolls',
        'Building Blocks',

        'Car Batteries',
        'Tires',
        'Motorcycle Helmets',
        'Brake Pads',

        'Others',
    ];

    protected $fillable = [
        'product_name',
        'user_id',
        'product_available_quantity',
        'product_price',
        'product_picture',
        'product_type',
        'product_description',
        'product_category',
        'product_meta_data',
    ];

    protected $casts = [
        'product_meta_data' => 'array',
        'product_price' => 'decimal:2',
    ];

    public function seller()
    {
        return $this->belongsTo(
            User::class,
            'user_id',
            'user_id'
        );
    }

    public function cartItems()
    {
        return $this->hasMany(Cart::class, 'product_id', 'product_id');
    }
}
