<?php

use App\Models\Product;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

test('database seeder creates role-specific users and seller-owned products', function () {
    $firstNewUserId = (User::query()->max('user_id') ?? 0) + 1;
    $firstNewProductId = (Product::query()->max('product_id') ?? 0) + 1;

    $this->seed(DatabaseSeeder::class);

    expect(User::query()->where('user_id', '>=', $firstNewUserId)->where('user_type', 'admin')->count())->toBe(5)
        ->and(User::query()->where('user_id', '>=', $firstNewUserId)->where('user_type', 'seller')->count())->toBe(15)
        ->and(User::query()->where('user_id', '>=', $firstNewUserId)->where('user_type', 'buyer')->count())->toBe(30)
        ->and(Product::query()->where('product_id', '>=', $firstNewProductId)->count())->toBe(50)
        ->and(Product::query()
            ->where('product_id', '>=', $firstNewProductId)
            ->whereHas('seller', fn (Builder $query): Builder => $query->where('user_type', '!=', 'seller'))
            ->count())->toBe(0);
});
