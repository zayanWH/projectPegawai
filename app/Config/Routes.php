<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Login::index');

//Routes login
$routes->get('login', 'Login::index');
$routes->post('login/proses', 'Login::proses');
$routes->get('logout', 'Login::logout');

// Rute untuk halaman Akses Ditolak (penting!)
$routes->get('akses-ditolak', function() {
    echo '<h1>Akses Ditolak!</h1><p>Maaf, Anda tidak memiliki izin untuk mengakses halaman ini.</p>';
    echo '<p><a href="' . base_url('login') . '">Kembali ke Halaman Login</a></p>';
});

// Routes Admin 
$routes->group('admin', ['filter' => 'auth:1'], function ($routes) {
    $routes->get('dashboard', 'DokumenControllerAdmin::index');
    $routes->get('manajemen-user', 'DokumenControllerAdmin::manajemenUser');
    $routes->get('manajemen-jabatan', 'DokumenControllerAdmin::manajemenJabatan');
    $routes->get('monitoring-storage', 'DokumenControllerAdmin::monitoringStorage');
    $routes->get('log-akses-file', 'DokumenControllerAdmin::logAksesFile');
});

// Routes HRD 
$routes->group('hrd', ['filter' => 'auth:2'], function ($routes) {
    $routes->get('dashboard', 'DokumenControllerHRD::index');
    $routes->get('dokumen-staff', 'DokumenControllerHRD::dokumenStaff');
    $routes->get('dokumen-spv', 'DokumenControllerHRD::dokumenSPV');
    $routes->get('dokumen-manager', 'DokumenControllerHRD::dokumenManager');
    $routes->get('dokumen-direksi', 'DokumenControllerHRD::dokumenDireksi');
    $routes->get('dokumen-umum', 'DokumenControllerHRD::dokumenUmum');
    $routes->get('dokumen-bersama', 'DokumenControllerHRD::dokumenBersama');
    $routes->get('aktivitas', 'DokumenControllerHRD::aktivitas');
});

// Routes untuk SPV 
$routes->group('supervisor', ['filter' => 'auth:5'], function ($routes) {
    $routes->get('dashboard', 'DokumenControllerSPV::index');
    $routes->get('dokumen-supervisor', 'DokumenControllerSPV::dokumenSPV');
    $routes->get('view-folder/(:num)', 'DokumenControllerSPV::viewFolder/$1');
    $routes->post('create-folder', 'DokumenControllerSPV::createFolder'); 
    $routes->post('upload-file', 'DokumenControllerSPV::uploadFile');
    $routes->get('download-file/(:num)', 'DokumenControllerSPV::downloadFile/$1');
    $routes->get('delete-file/(:num)', 'DokumenControllerSPV::deleteFile/$1');
    $routes->get('dokumen-bersama', 'DokumenControllerSPV::dokumenBersama');
    $routes->get('dokumen-umum', 'DokumenControllerSPV::dokumenUmum');
});

// Routes untuk Manager 
$routes->group('manager', ['filter' => 'auth:4'], function ($routes) {
    $routes->get('dashboard', 'DokumenControllerManager::index');
    $routes->get('dokumen-manager', 'DokumenControllerManager::dokumenManager');
    $routes->get('view-folder/(:num)', 'DokumenControllerManager::viewFolder/$1');
    $routes->post('create-folder', 'DokumenControllerManager::createFolder'); 
    $routes->post('upload-file', 'DokumenControllerManager::uploadFile');
    $routes->get('download-file/(:num)', 'DokumenControllerManager::downloadFile/$1');
    $routes->get('delete-file/(:num)', 'DokumenControllerManager::deleteFile/$1');
    $routes->get('dokumen-bersama', 'DokumenControllerManager::dokumenBersama');
    $routes->get('dokumen-umum', 'DokumenControllerManager::dokumenUmum');
});

// Routes untuk Direksi 
$routes->group('direksi', ['filter' => 'auth:3'], function ($routes) {
    $routes->get('dashboard', 'DokumenControllerDireksi::index');
    $routes->get('dokumen-direksi', 'DokumenControllerDireksi::dokumenDireksi');
    $routes->get('view-folder/(:num)', 'DokumenControllerDireksi::viewFolder/$1');
    $routes->post('create-folder', 'DokumenControllerDireksi::createFolder'); 
    $routes->post('upload-file', 'DokumenControllerDireksi::uploadFile');
    $routes->get('download-file/(:num)', 'DokumenControllerDireksi::downloadFile/$1');
    $routes->get('delete-file/(:num)', 'DokumenControllerDireksi::deleteFile/$1');
    $routes->get('dokumen-bersama', 'DokumenControllerDireksi::dokumenBersama');
    $routes->get('dokumen-umum', 'DokumenControllerDireksi::dokumenUmum');
});

// Routes untuk Staff 
$routes->group('staff', ['filter' => 'auth:6'], function ($routes) {
    $routes->get('dashboard', 'DokumenControllerStaff::dashboard');
    $routes->post('search', 'DokumenControllerStaff::search');
    $routes->get('dokumen-staff', 'DokumenControllerStaff::dokumenStaff');
    $routes->get('folder/(:num)', 'DokumenControllerStaff::viewFolder/$1');
    $routes->get('view-file/(:num)', 'DokumenControllerStaff::viewFile/$1');
    $routes->get('serve-file/(:num)', 'DokumenControllerStaff::serveFile/$1');
    $routes->post('create-folder', 'DokumenControllerStaff::createFolder');
    $routes->post('upload-file', 'DokumenControllerStaff::uploadFile');
    $routes->post('upload-from-folder', 'DokumenControllerStaff::uploadFromFolder');
    $routes->get('download-file/(:num)', 'DokumenControllerStaff::downloadFile/$1');
    $routes->get('delete-file/(:num)', 'DokumenControllerStaff::deleteFile/$1'); 
    $routes->get('dokumen-bersama', 'DokumenControllerStaff::dokumenBersama'); 
    $routes->get('dokumen-umum', 'DokumenControllerStaff::dokumenUmum');
    $routes->post('search', 'DokumenControllerStaff::search');
     $routes->get('view-file/(:num)', 'DokumenControllerStaff::viewFile/$1');
});

$routes->post('folder/rename/(:num)', 'Folder::rename/$1');
$routes->post('upload/doUpload', 'Upload::doUpload');

//delete folder
$routes->post('folders/delete', 'Folder::delete');
//rename folder
$routes->post('folders/rename', 'Folder::rename');
$routes->get('folder/download/(:num)', 'Folder::download/$1');