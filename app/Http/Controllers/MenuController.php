<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function index()
    {
        $menus = Menu::all();

        return view('menus.index', compact('menus'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|max:255',
            'description' => 'nullable',
        ]);

        $maxOrder = Menu::max('order') ?? 0;
        $validatedData['order'] = $maxOrder + 1;

        $menu = Menu::create($validatedData);

        return response()->json(['success' => true, 'menu' => $menu]);
    }

    public function show(Menu $menu)
    {
        $menu->load('categories.items');

        return view('menus.show', compact('menu'));
    }

    public function update(Request $request, Menu $menu)
    {
        $validatedData = $request->validate([
            'name' => 'required|max:255',
            'description' => 'nullable',
        ]);

        $menu->update($validatedData);

        return response()->json(['success' => true, 'menu' => $menu]);
    }

    public function destroy(Menu $menu)
    {
        try {
            $menu->delete();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error deleting menu'], 500);
        }
    }

    public function reorder(Request $request)
    {
        $request->validate([
            'order' => 'required|array',
            'order.*.id' => 'required|exists:menus,id',
            'order.*.order' => 'required|integer|min:0',
        ]);

        foreach ($request->order as $item) {
            Menu::where('id', $item['id'])->update(['order' => $item['order']]);
        }

        return response()->json(['success' => true]);
    }
}
