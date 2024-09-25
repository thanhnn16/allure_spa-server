<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class MobileAppController extends Controller
{
    public function chat()
    {
        return Inertia::render('MobileApp/Chat');
    }

    public function banners()
    {
        return Inertia::render('MobileApp/Banners');
    }

    public function support()
    {
        return Inertia::render('MobileApp/Support');
    }
}
