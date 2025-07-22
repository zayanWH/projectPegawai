<?php

namespace App\Controllers;

class DokumenControllerSPV extends BaseController
{
    public function index()
    {
        return view('Supervisor/dashboard');
    }

    public function dokumenSPV()
    {
        return view('Supervisor/dokumenSupervisor');
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
        return view('Supervisor/viewFolder');
    }
}