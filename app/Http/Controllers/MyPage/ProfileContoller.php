<?php

namespace App\Http\Controllers\MyPage;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileContoller extends Controller
{
    public function showProfileEdit()
    {
        return view('mypage.profile_edit_form', compact('user', Auth::user()));
    }
}
