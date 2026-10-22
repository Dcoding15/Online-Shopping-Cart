<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    /**
     * Admin dashboard with high-level marketplace metrics.
     */
    public function dashboard()
    {
        $totalUsers = User::count();
        $totalBuyers = User::where('user_type', 'buyer')->count();
        $totalSellers = User::where('user_type', 'seller')->count();
        $totalAdmins = User::where('user_type', 'admin')->count();

        $totalProducts = Product::count();
        $inStockProducts = Product::where('product_available_quantity', '>', 0)->count();
        $outOfStockProducts = Product::where('product_available_quantity', '<=', 0)->count();

        $totalOrders = Order::count();
        $paidOrders = Order::where('order_status', 'paid')->count();
        $totalRevenue = Order::where('order_status', 'paid')->sum('order_total_amount');

        return view('admin.dashboard', compact(
            'totalUsers',
            'totalBuyers',
            'totalSellers',
            'totalAdmins',
            'totalProducts',
            'inStockProducts',
            'outOfStockProducts',
            'totalOrders',
            'paidOrders',
            'totalRevenue'
        ));
    }

    /**
     * Manage platform users (admin, sellers, buyers).
     */
    public function users(Request $request)
    {
        $query = User::withCount('products', 'orders');

        if ($request->filled('type')) {
            $query->where('user_type', $request->type);
        }

        if ($request->filled('search')) {
            $search = '%'.trim($request->search).'%';
            $query->where(function ($q) use ($search) {
                $q->where('user_name', 'ilike', $search)
                    ->orWhere('phone_no', 'ilike', $search);
            });
        }

        $users = $query->orderBy('user_id', 'asc')->paginate(15)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    /**
     * Edit user profile, role, and wallet balance.
     */
    public function editUser($id)
    {
        $user = User::findOrFail($id);

        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update user details and wallet amount.
     */
    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'user_name' => 'required|string|max:100',
            'phone_no' => 'required|string|max:150|unique:users,phone_no,'.$user->user_id.',user_id',
            'user_type' => 'required|in:admin,seller,buyer',
            'user_addr' => 'nullable|string|max:255',
            'user_wallet_amount' => 'required|numeric|min:0',
        ]);

        $user->update([
            'user_name' => $validated['user_name'],
            'phone_no' => $validated['phone_no'],
            'user_type' => $validated['user_type'],
            'user_addr' => $validated['user_addr'] ?? null,
            'user_wallet_amount' => $validated['user_wallet_amount'],
        ]);

        return redirect()
            ->route('admin.users.index')
            ->with('success', "User '{$user->user_name}' updated successfully.");
    }

    /**
     * Remove a user from the platform.
     */
    public function destroyUser($id)
    {
        $user = User::findOrFail($id);

        if ($user->user_id === Auth::id()) {
            return redirect()
                ->route('admin.users.index')
                ->with('error', 'You cannot delete your own admin account.');
        }

        if ($user->products()->count() > 0) {
            return redirect()
                ->route('admin.users.index')
                ->with('error', "Cannot delete user '{$user->user_name}' because they have {$user->products()->count()} active product(s).");
        }

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }

    /**
     * Manage platform sellers with product and sales counts.
     */
    public function sellers()
    {
        $sellers = User::where('user_type', 'seller')
            ->withCount('products')
            ->orderBy('user_id', 'asc')
            ->paginate(15);

        return view('admin.users.sellers', compact('sellers'));
    }

    /**
     * Manage platform buyers with orders count.
     */
    public function buyers()
    {
        $buyers = User::where('user_type', 'buyer')
            ->withCount('orders')
            ->orderBy('user_id', 'asc')
            ->paginate(15);

        return view('admin.users.buyers', compact('buyers'));
    }

    /**
     * Platform-wide product oversight.
     */
    public function products(Request $request)
    {
        $query = Product::with('seller');

        if ($request->filled('category')) {
            $query->where('product_category', $request->category);
        }

        if ($request->filled('search')) {
            $search = '%'.trim($request->search).'%';
            $query->where('product_name', 'ilike', $search);
        }

        $products = $query->orderBy('product_id', 'desc')->paginate(15)->withQueryString();
        $categories = Product::CATEGORIES;

        return view('admin.products.index', compact('products', 'categories'));
    }

    /**
     * Delete a product as admin.
     */
    public function destroyProduct($id)
    {
        $product = Product::findOrFail($id);
        $productName = $product->product_name;

        $product->delete();

        return redirect()
            ->route('admin.products.index')
            ->with('success', "Product '{$productName}' removed successfully.");
    }

    /**
     * Platform-wide orders history.
     */
    public function orders()
    {
        $orders = Order::with(['buyer', 'items'])
            ->orderBy('order_id', 'desc')
            ->paginate(15);

        return view('admin.orders.index', compact('orders'));
    }

    /**
     * View detailed order invoice as admin.
     */
    public function showOrder($id)
    {
        $order = Order::with(['buyer', 'items.seller', 'items.product'])->findOrFail($id);

        return view('admin.orders.show', compact('order'));
    }
}
