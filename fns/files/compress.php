<?php
$result = false;
if (isset($data['compress']) && !empty($data['compress']) && isset($data['saveas']) && !empty($data['saveas'])) {
    if (isset($data['real_path']) && $data['real_path']) {
        $compress = $data['compress'];
        $saveas = $data['saveas'];
    } else {
        $compress = 'assets/files/'.$data['compress'];
        $saveas = 'assets/files/'.$data['saveas'];
    }

    if (file_exists($compress) && !file_exists($saveas)) {
        $compressPath = realpath($compress);
        $zip = new ZipArchive();
        $zip->open($saveas, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        if (is_dir($compress)) {
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($compressPath),
                RecursiveIteratorIterator::LEAVES_ONLY
            );
            foreach ($files as $name => $file) {
                if (!$file->isDir()) {
                    $filePath = $file->getRealPath();
                    $relativePath = substr($filePath, strlen($compressPath) + 1);
                    $zip->addFile($filePath, $relativePath);
                }}
        } else {
            $zip->addFile($compress, basename($compress));
            if (isset($arg[3])) {
                $zip->renameName(basename($compress), $arg[3]);
            }
        }
        $zip->close();
        $result = true;
    }
}
?>