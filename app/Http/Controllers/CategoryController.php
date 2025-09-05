<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    // Create Category
    public function store(Request $request)
    {
        $category = Category::create($request->all());
        return response()->json($category, 201);
    }

    // Get All Categories
    public function index()
    {
        $categories = Category::all();
        return response()->json($categories);
    }

    // Update Category
    public function update(Request $request, $id)
    {
        $category = Category::find($id);
        $category->update($request->all());
        return response()->json($category);
    }

    // Delete Category
    public function destroy($id)
    {
        Category::destroy($id);
        return response()->json(null, 204);
    }
}
