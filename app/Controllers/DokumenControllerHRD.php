<?php

namespace App\Controllers;

class DokumenControllerHRD extends BaseController
{
    public function index()
    {
        return view('HRD/dashboard');
    }

    public function dokumenStaff()
    {
        return view('HRD/dokumenStaff');
    }

    public function dokumenSPV()
    {
        return view('HRD/dokumenSPV');
    }
    public function dokumenManager()
    {
        return view('HRD/dokumenManager');
    }
    public function dokumenDireksi()
    {
        return view('HRD/dokumenDireksi');
    }

    public function dokumenBersama()
    {
        return view('HRD/dokumenBersama');
    }

    public function dokumenUmum()
    {
        return view('HRD/dokumenUmum');
    }

    public function aktivitas()
    {
        return view('HRD/aktivitas');
    }
}