<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    /**
     * Display a listing of the menus.
     *
     * @return \Illuminate\Http\Response
     */
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

        $menu = Menu::create($validatedData);

        return response()->json(['success' => true, 'menu' => $menu]);
    }

    /**
     * Display the specified menu.
     *
     * @return \Illuminate\Http\Response
     */
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
        $menu->delete();

        return response()->json(['success' => true]);
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
