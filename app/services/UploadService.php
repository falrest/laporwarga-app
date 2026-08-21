<?php
/**
 * Layanan upload file yang aman (section 10 & 26).
 * - Validasi MIME (bukan hanya ekstensi)
 * - Validasi ukuran maksimal (configurable)
 * - Nama file random, tidak pernah pakai nama asli
 * - getimagesize() untuk memverifikasi file gambar benar-benar gambar
 * - Menolak ekstensi executable/berbahaya
 */

class UploadService
{
    private const DANGEROUS_EXT = ['php', 'phtml', 'php3', 'php4', 'php5', 'php7', 'phar', 'pl', 'py', 'cgi', 'asp', 'aspx', 'exe', 'sh', 'htaccess'];

    /**
     * @param array  $file      Satu entri dari $_FILES
     * @param string $subfolder 'reports' | 'profiles' | 'evidence'
     * @return array{path:string, name:string, type:string, size:int}
     * @throws RuntimeException jika validasi gagal
     */
    public static function handle(array $file, string $subfolder): array
    {
        if (!isset($file['error']) || is_array($file['error'])) {
            throw new RuntimeException('Parameter upload tidak valid.');
        }

        switch ($file['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_NO_FILE:
                throw new RuntimeException('Tidak ada file yang diunggah.');
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                throw new RuntimeException('Ukuran file melebihi batas maksimal.');
            default:
                throw new RuntimeException('Terjadi kesalahan saat mengunggah file.');
        }

        if ($file['size'] > UPLOAD_MAX_SIZE) {
            $maxMb = round(UPLOAD_MAX_SIZE / (1024 * 1024));
            throw new RuntimeException("Ukuran file maksimal {$maxMb}MB.");
        }

        $originalExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (in_array($originalExt, self::DANGEROUS_EXT, true)) {
            throw new RuntimeException('Tipe file tidak diizinkan.');
        }

        $allowedExt = array_merge(UPLOAD_ALLOWED_IMAGE, UPLOAD_ALLOWED_VIDEO);
        if (!in_array($originalExt, $allowedExt, true)) {
            throw new RuntimeException('Format file harus JPG, PNG, atau MP4.');
        }

        // Validasi MIME sungguhan via fileinfo, jangan percaya $file['type'] dari client.
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $realMime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($realMime, UPLOAD_ALLOWED_MIME, true)) {
            throw new RuntimeException('Isi file tidak sesuai dengan format yang diizinkan.');
        }

        $isImage = in_array($originalExt, UPLOAD_ALLOWED_IMAGE, true);
        if ($isImage) {
            // Pastikan file benar-benar gambar valid, bukan file lain yang di-rename.
            $imageInfo = @getimagesize($file['tmp_name']);
            if ($imageInfo === false) {
                throw new RuntimeException('File gambar tidak valid atau rusak.');
            }
        }

        if (!is_uploaded_file($file['tmp_name'])) {
            throw new RuntimeException('Upload tidak valid.');
        }

        // Nama file random - JANGAN PERNAH pakai nama asli sebagai nama penyimpanan.
        $randomName = bin2hex(random_bytes(16)) . '.' . $originalExt;

        $baseDir = dirname(__DIR__, 2) . '/uploads/' . $subfolder;
        if (!is_dir($baseDir)) {
            mkdir($baseDir, 0755, true);
        }

        $destination = $baseDir . '/' . $randomName;
        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            throw new RuntimeException('Gagal menyimpan file.');
        }

        return [
            'path' => 'uploads/' . $subfolder . '/' . $randomName, // path relatif, disimpan di DB
            'name' => $randomName,
            'type' => $realMime,
            'size' => (int) $file['size'],
        ];
    }
}
