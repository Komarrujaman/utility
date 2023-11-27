<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\FirstUplink;
use App\Models\lastUplink;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    public function index()
    {

        $device = Device::uplink();
        // dd($first);
        return view('home', compact('device'));
    }
}
