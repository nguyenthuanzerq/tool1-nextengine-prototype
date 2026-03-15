<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use Illuminate\Http\Request;

class NextEngineConectionController extends Controller
{
    public function store(Request $request,$id){
        $shop = Shop::findOrFail($id);

        $validated = $request->validate([
            'client_id' => 'required|string|max:255',
            'client_secret' => 'required|string|max:255',
        ]);
        
        $shop->update($validated);
        
        return redirect()->route("btoc.shop.edit", ['id' => $shop->id]);
    }
}
