<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|max:255',
            'description' => 'nullable',
            'menu_id' => 'required|exists:menus,id',
        ]);

        $category = Category::create($validatedData);

        return response()->json(['success' => true, 'category' => $category]);
    }

    public function update(Request $request, Category $category)
    {
        $validatedData = $request->validate([
            'name' => 'required|max:255',
            'description' => 'nullable',
        ]);

        $category->update($validatedData);

        return response()->json(['success' => true, 'category' => $category]);
    }

    public function reorder(Request $request)
    {
        $request->validate([
            'order' => 'required|array',
            'order.*.id' => 'required|exists:categories,id',
            'order.*.order' => 'required|integer|min:0',
        ]);

        foreach ($request->order as $item) {
            Category::where('id', $item['id'])->update(['order' => $item['order']]);
        }

        return response()->json(['message' => 'Categories reordered successfully']);
    }

    public function destroy(Category $category)
    {
        $category->delete();

        return response()->json(['success' => true]);
    }
}
