<?= $this->extend('layout/hrd') ?>

<?= $this->section('content') ?>
<div class="bg-white rounded-lg shadow-sm p-6 mb-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <h1 class="text-2xl font-semibold text-gray-800">Dashboard</h1>
        </div>
        <div class="flex items-center space-x-4">
            <div class="relative">
                <input type="text"
                       placeholder="Masukkan file dokumen..."
                       class="w-80 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <svg class="absolute right-3 top-2.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
            <img src="<?= base_url('images/logo.png') ?>" alt="Logo USSI" class="h-10 w-auto rounded-lg">
        </div>
    </div>
</div>

<div class="relative inline-block text-left mb-6">
  <button id="dropdownButton" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium flex items-center space-x-2">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
    </svg>
    <span>Baru</span>
  </button>

  <div id="dropdownMenu" class="absolute z-10 mt-2 w-48 bg-white border border-gray-200 rounded-lg shadow-lg hidden">
    <a href="#" id="openCreateFolder" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">📁 Buat Folder</a>
    <a href="#" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">⬆️ Upload File</a>
    <a href="#" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">📂 Upload Folder</a>
  </div>
</div>

<div id="modalCreateFolder" class="fixed inset-0 z-50 flex items-center justify-center bg-black/10 backdrop-blur-sm hidden">
  <div class="bg-white p-6 rounded-lg shadow-xl w-full max-w-md transition-all duration-300 ease-in-out">
    <h2 class="text-xl font-semibold mb-4">Folder Baru</h2>

    <label class="block text-sm font-medium mb-1">Jenis Folder</label>
    <div class="relative mb-4">
      <select id="folderType" class="w-full border rounded-lg px-3 py-2 pr-10 appearance-none">
        <option disabled selected>Pilih jenis folder</option>
        <option value="personal">Personal Folder</option>
        <option value="shared">Shared Folder</option>
      </select>
      <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-gray-400">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
        </svg>
      </div>
    </div>

    <div class="grid grid-cols-2 gap-2 mb-4">
      <label><input type="checkbox" value="Staff" class="mr-2"> Staff</label>
      <label><input type="checkbox" value="Manager" class="mr-2"> Manager</label>
      <label><input type="checkbox" value="Supervisor" class="mr-2"> Supervisor</label>
      <label><input type="checkbox" value="Direksi" class="mr-2"> Direksi</label>
    </div>

    <label class="block text-sm font-medium mb-1">Akses</label>
    <div class="relative mb-4">
      <select id="folderAccess" class="w-full border rounded-lg px-3 py-2 pr-10 appearance-none">
        <option disabled selected>Pilih akses</option>
        <option value="full">Full Access</option>
        <option value="read">Read Only</option>
      </select>
      <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-gray-400">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
        </svg>
      </div>
    </div>

    <label class="block text-sm font-medium">Nama Folder</label>
    <input type="text" id="folderName" placeholder="Masukan nama folder" class="w-full border rounded-lg px-3 py-2 mb-4">

    <div class="flex justify-end space-x-4">
      <button id="cancelModal" class="text-blue-500">Batal</button>
      <button id="createFolderBtn" class="text-blue-600 font-semibold">Buat</button>
    </div>
  </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-blue-500">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 7a2 2 0 012-2h5l2 2h7a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V7z" />
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm text-gray-600">Total Folder</p>
                <p class="text-2xl font-semibold text-gray-800">10</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-green-500">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M7 3h8l5 5v13a2 2 0 01-2 2H7a2 2 0 01-2-2V5a2 2 0 012-2z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M14 3v5h5" />
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm text-gray-600">Total File</p>
                <p class="text-2xl font-semibold text-gray-800">100</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-yellow-500">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 3h8l5 5v13a2 2 0 01-2 2H7a2 2 0 01-2-2V5a2 2 0 012-2z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M14 3v5h5" />
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm text-gray-600">File HRD</p>
                <p class="text-2xl font-semibold text-gray-800">100</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-red-500"> <div class="flex items-center">
            <div class="flex-shrink-0">
                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M5.121 17.804A7 7 0 0112 15a7 7 0 016.879 2.804M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm text-gray-600">Total User</p>
                <p class="text-2xl font-semibold text-gray-800">250</p>
            </div>
        </div>
    </div>
</div>

<div class="bg-white rounded-lg shadow-sm">
    <div class="p-6 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-800 flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            Dokumen Terbaru
        </h2>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-blue-600">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Nama File</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Folder</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Pengunggah</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Tanggal Diunggah</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <svg class="w-5 h-5 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"></path>
                    </svg>
                    <span class="text-sm text-gray-900">laporan.pdf</span>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <svg class="w-5 h-5 text-gray-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M2 6a2 2 0 012-2h5l2 2h7a2 2 0 012 2v7a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" />
                    </svg>

                    <span class="text-sm text-gray-900">I. SOP</span>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="w-5 h-5 bg-red-500 rounded-full mr-2"></div>
                    <span class="text-sm text-gray-900">user@gmail.com</span>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">2 Jul 2024</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"> <button onclick="toggleMenu(this)" class="text-blue-600 hover:text-blue-900">⋮</button> </td>
              </tr>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>