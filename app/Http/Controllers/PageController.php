<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function home()
    {
        return view('pages.home');
    }

    public function men()
    {
        return view('pages.men');
    }

    public function women()
    {
        return view('pages.women');
    }

    public function kids()
    {
        return view('pages.kids');
    }

    public function sale()
    {
        return view('pages.sale');
    }

    public function new()
    {
        return view('pages.new');
    }

    
}
    