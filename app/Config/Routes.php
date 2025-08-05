<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Login::index');

// Routes login
$routes->get('login', 'Login::index');
$routes->post('login/proses', 'Login::proses');
$routes->get('logout', 'Login::logout');

// Rute untuk halaman Akses Ditolak
$routes->get('akses-ditolak', function () {
    echo '<h1>Akses Ditolak!</h1><p>Maaf, Anda tidak memiliki izin untuk mengakses halaman ini.</p>';
    echo '<p><a href="' . base_url('login') . '">Kembali ke Halaman Login</a></p>';
});

// Routes Admin - Filtered by AuthFilter for role_id 1
$routes->group('admin', ['filter' => 'auth:1'], function ($routes) {
    $routes->get('dashboard', 'DokumenControllerAdmin::index');
    $routes->get('manajemen-user', 'DokumenControllerAdmin::manajemenUser');
    $routes->get('manajemen-jabatan', 'DokumenControllerAdmin::manajemenJabatan');
    $routes->get('monitoring-storage', 'DokumenControllerAdmin::monitoringStorage');
    $routes->get('getStorageByPosition', 'DokumenControllerAdmin::getStorageByPosition');
    $routes->get('getTopStorageUsers', 'DokumenControllerAdmin::getTopStorageUsers');
    $routes->get('getLargestFiles', 'DokumenControllerAdmin::getLargestFiles');
    $routes->get('log-akses-file', 'DokumenControllerAdmin::logAksesFile');
    $routes->get('users', 'AdminController::users');
    $routes->get('users/edit/(:num)', 'DokumenControllerAdmin::getUserForEdit/$1'); 
    $routes->post('users/update', 'DokumenControllerAdmin::updateUser');
    $routes->post('users/add', 'DokumenControllerAdmin::addUser'); 
    $routes->post('users/delete', 'DokumenControllerAdmin::deleteUser'); 
    $routes->get('jabatan/edit/(:num)', 'DokumenControllerAdmin::getRoleForEdit/$1'); 
    $routes->post('jabatan/update', 'DokumenControllerAdmin::updateJabatan'); 
    $routes->post('jabatan/delete', 'DokumenControllerAdmin::deleteJabatan');
    $routes->post('jabatan/add', 'DokumenControllerAdmin::addJabatan'); 
    $routes->get('roles', 'DokumenControllerAdmin::getRoles'); 
    $routes->get('search-users', 'DokumenControllerAdmin::searchUsers');
    $routes->get('getStorageByRole', 'DokumenControllerAdmin::getStorageByRole');
    $routes->get('log-akses-file', 'DokumenControllerAdmin::logAksesFile');
});

$routes->group('hrd', ['filter' => 'auth'], function ($routes) {
    $routes->get('dashboard', 'DashboardController::index');
    $routes->get('dokumen-umum', 'DokumenControllerHRD::dokumenUmum');
    $routes->get('dokumen-umum/folder/(:num)', 'DokumenControllerHRD::dokumenUmumFolder/$1');
    $routes->post('dokumen-umum/create-folder', 'DokumenControllerHRD::createFolderUmum');
    $routes->post('dokumen-umum/upload-file', 'DokumenControllerHRD::uploadFileUmum');
    $routes->get('createFolder', 'DokumenControllerHRD::createFolder');
    $routes->post('createFolder', 'DokumenControllerHRD::createFolder');
    $routes->get('manageFolder', 'DokumenControllerHRD::manageFolder');
    $routes->post('manageFolder', 'DokumenControllerHRD::manageFolder');
    $routes->get('uploadFile', 'DokumenControllerHRD::uploadFile');
    $routes->post('uploadFile', 'DokumenControllerHRD::uploadFile');
    $routes->get('manageFile', 'DokumenControllerHRD::manageFile');
    $routes->post('manageFile', 'DokumenControllerHRD::manageFile');
    $routes->get('shareDocument', 'DokumenControllerHRD::shareDocument');
    $routes->post('shareDocument', 'DokumenControllerHRD::shareDocument');
});

$routes->group('hrd', ['filter' => 'auth:2'], function ($routes) {
    $routes->get('dashboard', 'DokumenControllerHRD::index');
    $routes->get('dokumen-staff', 'DokumenControllerHRD::dokumenStaff');
    $routes->get('dokumen-spv', 'DokumenControllerHRD::dokumenSPV');
    $routes->get('dokumen-direksi', 'DokumenControllerHRD::dokumenDireksi');
    $routes->get('aktivitas', 'DokumenControllerHRD::ActivityLogs', ['as' => 'hrd_activity_logs']);
    $routes->get('view-staff-folder/(:num)', 'DokumenControllerHRD::viewStaffFolder/$1');
    $routes->post('search', 'DokumenControllerHRD::search');
    $routes->get('dokumen-spv', 'DokumenControllerHRD::dokumenSpv');
    $routes->get('dokumen-manager', 'DokumenControllerHRD::dokumenManager');

    $routes->post('create-folder', 'DokumenControllerHRD::createFolder');
    $routes->post('upload-file', 'DokumenControllerHRD::uploadFile');
    $routes->get('file/download/(:num)', 'DokumenControllerHRD::downloadFile/$1');
    $routes->get('file/serve/(:num)', 'DokumenControllerHRD::serveFile/$1');
    $routes->get('file/view/(:num)', 'DokumenControllerHRD::viewFile/$1');
    $routes->post('hrd/createFolder', 'DokumenControllerHRD::createFolder');
    $routes->post('upload-from-folder', 'DokumenControllerHRD::uploadFromFolder');
    $routes->post('view-staff-folder/(:num)/api/upload-file', 'DokumenControllerHRD::uploadFile');
    $routes->get('dokumen-umum', 'DokumenControllerHRD::dokumenUmum');
    $routes->get('dokumen-umum/folder/(:num)', 'DokumenControllerHRD::dokumenUmumFolder/$1');
    $routes->post('dokumen-umum/create-folder', 'DokumenControllerHRD::createFolderUmum');
    $routes->post('dokumen-umum/upload-file', 'DokumenControllerHRD::uploadFileUmum');
    
    // Notification Routes - HRD Only
    $routes->get('notifications/dashboard', 'NotificationController::dashboard');
    $routes->post('notifications/test-system', 'NotificationController::testSystem');
    $routes->post('notifications/test-email', 'NotificationController::testEmail');
    $routes->post('notifications/simulate', 'NotificationController::simulateNotification');
    $routes->get('notifications/user', 'NotificationController::getUserNotifications');
    $routes->post('notifications/mark-read/(:num)', 'NotificationController::markAsRead/$1');
});

$routes->group('umum', ['filter' => 'auth:2,3,4,5,6'], function ($routes) { 
    $routes->get('dokumen-bersama', 'DokumenControllerStaff::dokumenBersama'); 
    $routes->get('dokumen-umum-staff', 'DokumenControllerStaff::dokumenUmum');       
    $routes->get('view-shared-folder/(:num)', 'Staff::viewSharedFolder/$1');   
});


// Routes untuk SPV
$routes->group('supervisor', ['filter' => 'auth:5'], function ($routes) {
    $routes->get('dashboard', 'DokumenControllerSPV::dashboard');
    $routes->get('dokumen-supervisor', 'DokumenControllerSPV::dokumenSPV');
    $routes->get('dokumen-staff', 'DokumenControllerSPV::dokumenStaffUntukSPV');
    $routes->get('dokumenStaff', 'DokumenControllerSPV::dokumenStaffUntukSPV');
    $routes->get('folder/(:num)', 'DokumenControllerSPV::viewFolder/$1');
    $routes->post('create-folder', 'DokumenControllerSPV::createFolder');
    $routes->post('upload-file', 'DokumenControllerSPV::uploadFile');
    $routes->get('download-file/(:num)', 'DokumenControllerSPV::downloadFile/$1');
    $routes->get('delete-file/(:num)', 'DokumenControllerSPV::deleteFile/$1');
    $routes->get('view-staff-folder/(:num)', 'DokumenControllerSPV::viewStaffFolder/$1');
    $routes->post('search', 'DokumenControllerSPV::search');
    $routes->post('searchStaff', 'DokumenControllerSPV::searchStaff');
});

// Routes untuk Manager
$routes->group('manager', ['filter' => 'auth:4'], function ($routes) {
    $routes->get('dashboard', 'DokumenControllerManager::dashboard');
    $routes->get('dokumen-manager', 'DokumenControllerManager::dokumenManager');
    $routes->get('view-folder/(:num)', 'DokumenControllerManager::viewFolder/$1');
    $routes->post('create-folder', 'DokumenControllerManager::createFolder');
    $routes->post('upload-file', 'DokumenControllerManager::uploadFile');
    $routes->get('download-file/(:num)', 'DokumenControllerManager::downloadFile/$1');
    $routes->get('delete-file/(:num)', 'DokumenControllerManager::deleteFile/$1');
    $routes->get('dokumen-staff', 'DokumenControllerManager::dokumenStaffUntukManager');
    $routes->get('dokumenStaff', 'DokumenControllerManager::dokumenStaffUntukManager');
    $routes->get('dokumen-supervisor', 'DokumenControllerManager::dokumenSPVUntukManager');
    $routes->get('dokumenSupervisor', 'DokumenControllerManager::dokumenSPVUntukManager');
    $routes->get('view-staff-folder/(:num)', 'DokumenControllerManager::viewStaffFolder/$1');
    $routes->get('view-supervisor-folder/(:num)', 'DokumenControllerManager::viewSPVFolder/$1');
});

// Routes untuk Direksi
$routes->group('direksi', ['filter' => 'auth:3'], function ($routes) {
    $routes->get('dashboard', 'DokumenControllerDireksi::dashboard');
    $routes->get('dokumen-direksi', 'DokumenControllerDireksi::dokumenDireksi');
    $routes->get('view-folder/(:num)', 'DokumenControllerDireksi::viewFolder/$1');
    $routes->post('create-folder', 'DokumenControllerDireksi::createFolder');
    $routes->post('upload-file', 'DokumenControllerDireksi::uploadFile');
    $routes->get('download-file/(:num)', 'DokumenControllerDireksi::downloadFile/$1');
    $routes->get('delete-file/(:num)', 'DokumenControllerDireksi::deleteFile/$1');
    $routes->get('dokumen-staff', 'DokumenControllerDireksi::dokumenStaffUntukDireksi');
    $routes->get('dokumenStaff', 'DokumenControllerDireksi::dokumenStaffUntukDireksi');
    $routes->get('dokumen-supervisor', 'DokumenControllerDireksi::dokumenSPVUntukDireksi');
    $routes->get('dokumenSupervisor', 'DokumenControllerDireksi::dokumenSPVUntukDireksi');
    $routes->get('dokumen-manager', 'DokumenControllerDireksi::dokumenManagerUntukDireksi');
    $routes->get('dokumenManager', 'DokumenControllerDireksi::dokumenManagerUntukDireksi');
    $routes->get('view-staff-folder/(:num)', 'DokumenControllerDireksi::viewStaffFolder/$1');
    $routes->get('view-supervisor-folder/(:num)', 'DokumenControllerDireksi::viewSPVFolder/$1');
    $routes->get('view-manager-folder/(:num)', 'DokumenControllerDireksi::viewManagerFolder/$1');
});

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
});

$routes->post('folders/create', 'Folder::create');
$routes->post('folders/rename/(:num)', 'Folder::rename/$1');
$routes->post('folders/delete', 'Folder::delete');
$routes->post('upload/doUpload', 'Upload::doUpload');
$routes->get('folder/download/(:num)', 'Folder::download/$1');
$routes->post('folders/rename', 'Folder::rename');
