<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Closure;
use Illuminate\Http\Request;

class LanguageController extends Controller
{
    public function store($lang) {

        session()->put('lang',$lang);
        return redirect()->back();
    }
}
