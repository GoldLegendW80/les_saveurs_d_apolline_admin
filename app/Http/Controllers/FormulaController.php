<?php

namespace App\Http\Controllers;

use App\Models\Formula;
use Illuminate\Http\Request;

class FormulaController extends Controller
{
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|max:255',
            'price' => 'required|numeric',
            'menu_id' => 'required|exists:menus,id',
        ]);

        $maxOrder = Formula::where('menu_id', $request->menu_id)->max('order') ?? 0;
        $validatedData['order'] = $maxOrder + 1;

        $formula = Formula::create($validatedData);

        return response()->json(['success' => true, 'formula' => $formula]);
    }

    public function update(Request $request, Formula $formula)
    {
        $validatedData = $request->validate([
            'name' => 'required|max:255',
            'price' => 'required|numeric',
        ]);

        $formula->update($validatedData);

        return response()->json(['success' => true, 'formula' => $formula]);
    }

    public function destroy(Formula $formula)
    {
        $formula->delete();

        return response()->json(['success' => true]);
    }

    public function reorder(Request $request)
    {
        $request->validate([
            'order' => 'required|array',
            'order.*.id' => 'required|exists:formulas,id',
            'order.*.order' => 'required|integer|min:0',
        ]);

        foreach ($request->order as $item) {
            Formula::where('id', $item['id'])->update(['order' => $item['order']]);
        }

        return response()->json(['message' => 'Formulas reordered successfully']);
    }
}
