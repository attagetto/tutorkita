<?php

namespace App\Http\Controllers;

use App\Support\LandingPageData;
use Illuminate\View\View;

class LandingController extends Controller
{
    public function __invoke(): View
    {
        return view('landing.index', LandingPageData::make());
    }
}
