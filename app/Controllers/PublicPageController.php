<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class PublicPageController extends BaseController
{
    public function home()
    {
        return view('public/home');
    }

    public function privacyPolicy()
    {
        return view('public/privacy_policy');
    }
}
