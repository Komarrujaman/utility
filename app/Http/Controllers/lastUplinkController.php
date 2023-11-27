<?php

namespace App\Http\Controllers;

use App\Models\lastUplink;
use Illuminate\Http\Request;

class lastUplinkController extends Controller
{

    public function index()
    {
        $last = lastUplink::getLastUplink();
        return redirect()->back();
    }
}
