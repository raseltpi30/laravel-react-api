<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        return Product::all()->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'category' => $product->category,
                'price' => $product->price,
                'quantity' => $product->quantity,
                'sale_percentage' => $product->sale_percentage,
                'img' => asset('uploads/products/' . $product->img), // Ensure full URL
            ];
        });
    }
    public function indexFront(Request $request)
    {
        $category = $request->query('category'); // Get category from query string

        $productsQuery = Product::query();

        if ($category) {
            // Filter by category if provided
            $productsQuery->where('category', $category);
        }

        return $productsQuery->get()->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'category' => $product->category,
                'price' => $product->price,
                'quantity' => $product->quantity,
                'sale_percentage' => $product->sale_percentage,
                'img' => asset('uploads/products/' . $product->img), // Ensure full URL
            ];
        });
    }



    public function store(Request $request)
    {
        // Validate the input data
        $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'img' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // Validate image
            'price' => 'required|numeric|min:0',
            'sale_percentage' => 'required|nullable|integer|min:0|max:100',
        ]);

        // Handle image upload
        if ($request->hasFile('img')) {
            $image = $request->file('img');
            $imageName = time() . '_' . $image->getClientOriginalName(); // Generate unique name
            $image->move(public_path('uploads/products'), $imageName); // Save to 'public/uploads/products'
        }

        // Create product
        $product = Product::create([
            'name' => $request->name,
            'category' => $request->category,
            'img' => $imageName, // Save image name in database
            'price' => $request->price,
            'quantity' => $request->quantity,
            'sale_percentage' => $request->sale_percentage,
        ]);

        return response()->json($product, 201);
    }



    public function show($id)
    {
        $product = Product::findOrFail($id);
        if (!$product) {
            return response()->json(['success' => 'Product not found'], 404);
        }
        return response()->json($product, 201);
    }

    public function update(Request $request, $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        // ✅ Validate incoming data (only provided fields will be validated)
        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'category' => 'sometimes|required|string|max:255',
            'price' => 'sometimes|required|numeric|min:0',
            'img' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'sale_percentage' => 'sometimes|nullable|numeric|min:0|max:100', // Allow null
        ]);

        // ✅ Handle image update if new image is uploaded
        if ($request->img) {
            $image = $request->img;
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('uploads/products'), $imageName);

            if (!empty($product->img) && file_exists(public_path('uploads/products/' . $product->img))) {
                unlink(public_path('uploads/products/' . $product->img));
            }
            $product->img = $imageName;
        }

        // ✅ Update other provided fields
        $product->update($request->except(['img']));
        // ✅ Save product (if image was updated manually)
        $product->save();

        return response()->json(['message' => 'Product updated successfully', 'product' => $product], 200);
    }



    public function destroy($id)
    {
        Product::destroy($id);
        return response()->json(['message' => 'Product deleted']);
    }
}
