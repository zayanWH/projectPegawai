<?php

namespace App\Controllers;

class DokumenControllerDireksi extends BaseController
{
    public function index()
    {
        return view('Direksi/dashboard');
    }

    public function dokumenDireksi()
    {
        return view('Direksi/dokumenDireksi');
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
        return view('Direksi/viewFolder');
    }
}