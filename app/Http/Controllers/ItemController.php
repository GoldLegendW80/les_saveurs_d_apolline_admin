<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|max:255',
            'description' => 'nullable',
            'price' => 'nullable|numeric',
            'category_id' => 'required|exists:categories,id',
        ]);

        $item = Item::create($validatedData);

        return response()->json(['success' => true, 'item' => $item]);
    }

    public function update(Request $request, Item $item)
    {
        $validatedData = $request->validate([
            'name' => 'required|max:255',
            'description' => 'nullable',
            'price' => 'required|numeric',
        ]);

        $item->update($validatedData);

        return response()->json(['success' => true, 'item' => $item]);
    }

    public function destroy(Item $item)
    {
        $item->delete();

        return response()->json(['success' => true]);
    }

    public function reorder(Request $request, $categoryId)
    {
        $request->validate([
            'order' => 'required|array',
            'order.*.id' => 'required|exists:items,id',
            'order.*.order' => 'required|integer|min:0',
        ]);

        foreach ($request->order as $itemOrder) {
            Item::where('id', $itemOrder['id'])
                ->where('category_id', $categoryId)
                ->update(['order' => $itemOrder['order']]);
        }

        return response()->json(['message' => 'Items reordered successfully']);
    }
}
