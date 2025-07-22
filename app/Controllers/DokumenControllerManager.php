<?php

namespace App\Controllers;

class DokumenControllerManager extends BaseController
{
    public function index()
    {
        return view('Manager/dashboard');
    }

    public function dokumenManager()
    {
        return view('Manager/dokumenManager');
    }

    public function dokumenBersama()
    {
        return view('Umum/dokumenBersama');
    }

    public function dokumenUmum()
    {
        return view('Umum/dokumenUmum');
    }

    public function viewFolder()
    {
        return view('Manager/viewFolder');
    }
}