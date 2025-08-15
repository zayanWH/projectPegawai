<?= $this->extend('layout/main') ?>

<?= $this->section('pageTitle') ?>
Dashboard
<?= $this->endSection() ?>

<?= $this->section('pageLogo') ?>
<img src="<?= base_url('images/logo.jpg') ?>" alt="Logo USSI" class="h-10 w-auto rounded-lg">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="hidden md:block">
    <!-- Header Dashboard -->
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <h1 class="text-2xl font-semibold text-gray-800">Dashboard</h1>
            </div>
            <div class="flex items-center space-x-4">
                <img src="<?= base_url('images/logo.jpg') ?>" alt="Logo USSI" class="h-10 w-auto rounded-lg">
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <!-- Card 1 -->
    <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-yellow-500">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <img src="<?= base_url('images/folder.png') ?>" alt="Folder Icon" class="w-8 h-7 mr-2">
            </div>
            <div class="ml-4">
                <p class="text-sm text-gray-600">Total Folder</p>
                <p class="text-2xl font-semibold text-gray-800"><?= esc($totalFolders) ?></p>
            </div>
        </div>
    </div>

    <!-- Card 2 -->
    <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-blue-500">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <img src="<?= base_url('images/file-default.png') ?>" alt="Folder Icon" class="w-8 h-7 mr-2">
            </div>
            <div class="ml-4">
                <p class="text-sm text-gray-600">Total File</p>
                <p class="text-2xl font-semibold text-gray-800"><?= esc($totalFiles) ?></p>
            </div>
        </div>
    </div>

    <!-- Card 3 -->
    <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-red-500">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm text-gray-600">Terakhir Upload</p>
                <p class="text-2xl font-semibold text-gray-800"><?= esc($latestUploadDate) ?></p>
            </div>
        </div>
    </div>
</div>

<div class="bg-white rounded-lg shadow-sm hidden md:block">
    <div class="overflow-x-auto">
        <table class="w-full">
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($recentItems)): ?>
                    <tr>
                        <td colspan="4" class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                            Tidak ada item terbaru untuk ditampilkan.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($recentItems as $item): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <?php if ($item['type'] === 'folder'): ?>
                                        <img src="<?= base_url('images/folder.png') ?>" alt="Folder Icon" class="w-5 h-5 mr-2">
                                    <?php else: ?>
                                        <?php
                                        $fileExtension = pathinfo($item['name'], PATHINFO_EXTENSION);
                                        $iconSrc = '';

                                        switch (strtolower($fileExtension)) {
                                            case 'pptx':
                                                $iconSrc = base_url('images/ppt.png');
                                                break;
                                            case 'docx':
                                                $iconSrc = base_url('images/word.png');
                                                break;
                                            case 'xlsx':
                                                $iconSrc = base_url('images/excel.png');
                                                break;
                                            case 'pdf':
                                                $iconSrc = base_url('images/pdf.png');
                                                break;
                                            case 'png':
                                            case 'jpg':
                                            case 'jpeg':
                                            case 'gif':
                                                $iconSrc = base_url('images/image.png');
                                                break;
                                            default:
                                                // Ikon default jika ekstensi tidak dikenali
                                                $iconSrc = base_url('images/file-default.png');
                                                break;
                                        }
                                        ?>
                                        <img src="<?= $iconSrc ?>" alt="<?= esc($fileExtension) ?> File Icon" class="w-5 h-5 mr-2">
                                    <?php endif; ?>
                                    <span class="text-sm text-gray-900"><?= esc($item['name']) ?></span>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="bg-white rounded-lg shadow-sm block md:hidden">
    <div class="divide-y divide-gray-200">
        <?php if (empty($recentItems)): ?>
            <div class="flex items-center justify-center px-6 py-4 text-gray-500">
                Tidak ada item terbaru untuk ditampilkan.
            </div>
        <?php else: ?>
            <?php foreach ($recentItems as $item): ?>
                <div class="flex items-center justify-between px-6 py-4 hover:bg-gray-50">
                    <div class="flex items-center space-x-4">
                        <?php if ($item['type'] === 'folder'): ?>
                            <img src="<?= base_url('images/folder.png') ?>" alt="Folder Icon" class="w-6 h-6 text-yellow-500">
                        <?php else: ?>
                            <?php
                            $fileExtension = pathinfo($item['name'], PATHINFO_EXTENSION);
                            $iconSrc = '';
                            switch (strtolower($fileExtension)) {
                                case 'pptx':
                                    $iconSrc = base_url('images/ppt.png');
                                    break;
                                case 'docx':
                                    $iconSrc = base_url('images/word.png');
                                    break;
                                case 'xlsx':
                                    $iconSrc = base_url('images/excel.png');
                                    break;
                                case 'pdf':
                                    $iconSrc = base_url('images/pdf.png');
                                    break;
                                case 'png':
                                case 'jpg':
                                case 'jpeg':
                                case 'gif':
                                    $iconSrc = base_url('images/image.png');
                                    break;
                                default:
                                    $iconSrc = base_url('images/file-default.png');
                                    break;
                            }
                            ?>
                            <img src="<?= $iconSrc ?>" alt="<?= esc($fileExtension) ?> File Icon" class="w-6 h-6 text-red-500">
                        <?php endif; ?>
                        <div class="text-sm">
                            <div class="font-nomral text-gray-900"><?= esc($item['name']) ?></div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>