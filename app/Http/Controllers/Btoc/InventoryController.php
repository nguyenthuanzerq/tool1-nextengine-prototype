<?php

namespace App\Http\Controllers\Btoc;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Btoc\InventoryService;
use App\Models\Shop;
use App\Models\Mall;
use App\Models\Channel;

class InventoryController extends Controller
{
    public function index()
    {

        return view('btoc.inventory.index');
    }

    
}