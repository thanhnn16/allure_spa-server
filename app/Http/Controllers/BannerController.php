<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class BannerController extends BaseController
{
    public function banners()
    {
        return Inertia::render('MobileApp/Banners');
    }
}
