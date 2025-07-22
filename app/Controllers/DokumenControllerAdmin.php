<?php

namespace App\Controllers;

class DokumenControllerAdmin extends BaseController
{
    public function index()
    {
        return view('Admin/dashboard');
    }

    public function manajemenUser()
    {
        return view('Admin/manajemenUser');
    }

    public function manajemenJabatan()
    {
        return view('Admin/manajemenJabatan');
    }

    public function monitoringStorage()
    {
        return view('Admin/monitoringStorage');
    }

    public function logAksesFile()
    {
        return view('Admin/logAksesFile');
    }
}