<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        return view(
            'customer.home',
            [
                'discoverUrl' =>
                    route('salons.discover'),

                'seoTitle' =>
                    'RM نوبت‌دهی | پیدا کن، انتخاب کن، نوبت بگیر',

                'seoDescription' =>
                    'با RM سالن، آرایشگر و خدمات زیبایی موردنظرت را پیدا کن و نوبت بگیر.',
            ]
        );
    }
}
