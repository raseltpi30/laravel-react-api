<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller {

    // ✅ 1. Get Cart Items
    public function getCartItems() {
        $cartItems = Cart::where('user_id', Auth::id())->get();
        return response()->json($cartItems);
    }

    // ✅ 2. Add Product to Cart
    public function addToCart(Request $request) {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1'
        ]);

        $product = Product::find($request->product_id);
        $cartItem = Cart::where('user_id', Auth::id())->where('product_id', $product->id)->first();

        if ($cartItem) {
            $cartItem->quantity += $request->quantity;
            $cartItem->save();
        } else {
            Cart::create([
                'user_id' => Auth::id(),
                'product_id' => $product->id,
                'product_name' => $product->name,
                'product_img' => $product->img,
                'product_price' => $product->price,
                'quantity' => $request->quantity
            ]);
        }

        return response()->json(['message' => 'Product added to cart']);
    }

    // ✅ 3. Update Quantity
    public function updateQuantity(Request $request, $id) {
        $request->validate(['quantity' => 'required|integer|min:1']);

        $cartItem = Cart::where('id', $id)->where('user_id', Auth::id())->first();

        if (!$cartItem) {
            return response()->json(['message' => 'Cart item not found'], 404);
        }

        $cartItem->quantity = $request->quantity;
        $cartItem->save();

        return response()->json(['message' => 'Quantity updated']);
    }

    // ✅ 4. Remove Product from Cart
    public function removeCartItem($id) {
        $cartItem = Cart::where('id', $id)->where('user_id', Auth::id())->first();

        if (!$cartItem) {
            return response()->json(['message' => 'Cart item not found'], 404);
        }

        $cartItem->delete();
        return response()->json(['message' => 'Product removed from cart']);
    }

    // ✅ 5. Clear Cart
    public function clearCart() {
        Cart::where('user_id', Auth::id())->delete();
        return response()->json(['message' => 'Cart cleared']);
    }
}
