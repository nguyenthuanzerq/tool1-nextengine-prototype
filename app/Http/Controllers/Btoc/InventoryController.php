<?php

namespace App\Http\Controllers\Btoc;

use App\Http\Controllers\Controller;
use App\Models\Channel;
use App\Models\Mall;
use App\Models\Shop;
use App\Services\Btoc\InventoryService;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index()
    {

        return view('btoc.inventory.index');
    }

    
}
