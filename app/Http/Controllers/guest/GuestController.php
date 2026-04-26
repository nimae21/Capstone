<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class GuestController extends Controller
{
    public function index()
    {
        // 🔥 Redirect if already logged in
        if (Auth::check()) {

            // If admin → go to admin dashboard
            if (Auth::user()->is_admin) {
                return redirect()->route('admin.dashboard');
            }

            // If normal user → go to home
            return redirect()->route('home');
        }

        // 👇 Only guests reach here
        $products = Product::with(['category', 'brand', 'images'])
            ->latest()
            ->paginate(6);

        return view('guest.index', compact('products'));
    }
}