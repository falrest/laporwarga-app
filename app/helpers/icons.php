<?php
/**
 * Kumpulan ikon outline (SVG inline) dipakai di berbagai halaman.
 * Kategori tetap berasal dari database — ini hanya memetakan kolom
 * `icon` (string key) ke bentuk SVG yang sesuai untuk ditampilkan.
 */

function category_icon(string $key): string
{
    $icons = [
        'infrastruktur' => '<path d="M3 21h18"/><path d="M5 21V7l7-4 7 4v14"/><path d="M9 21v-6h6v6"/>',
        'sampah'        => '<path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M6 6l1 14h10l1-14"/>',
        'lampu'         => '<circle cx="12" cy="9" r="6"/><path d="M9 21h6"/><path d="M10 17h4"/>',
        'pohon'         => '<path d="M12 2 6 12h4l-4 8h12l-4-8h4Z"/><path d="M12 20v2"/>',
        'drainase'      => '<path d="M2 12h20"/><path d="M6 12a4 4 0 0 1 4-4h4a4 4 0 0 1 4 4"/><path d="M12 12v8"/>',
        'transportasi'  => '<rect x="3" y="7" width="18" height="10" rx="2"/><circle cx="7.5" cy="17.5" r="1.5"/><circle cx="16.5" cy="17.5" r="1.5"/>',
        'pendidikan'    => '<path d="M22 10 12 5 2 10l10 5 10-5Z"/><path d="M6 12v5c0 1.5 2.7 3 6 3s6-1.5 6-3v-5"/>',
        'kesehatan'     => '<path d="M12 21s-7-4.5-9.5-9C.7 8.2 2 4.5 5.5 4c2-.3 3.7.8 4.5 2 .8-1.2 2.5-2.3 4.5-2 3.5.5 4.8 4.2 3 8-2.5 4.5-9.5 9-9.5 9Z"/>',
    ];

    $path = $icons[$key] ?? '<circle cx="12" cy="12" r="9"/>';
    return '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">' . $path . '</svg>';
}

function status_label(string $status): string
{
    $labels = [
        'diajukan'     => 'Diajukan',
        'diverifikasi' => 'Diverifikasi',
        'diteruskan'   => 'Diteruskan',
        'diproses'     => 'Sedang Diproses',
        'selesai'      => 'Selesai',
        'ditolak'      => 'Ditolak',
    ];
    return $labels[$status] ?? ucfirst($status);
}

function status_progress_percent(string $status): int
{
    $map = [
        'diajukan'     => 10,
        'diverifikasi' => 30,
        'diteruskan'   => 50,
        'diproses'     => 75,
        'selesai'      => 100,
        'ditolak'      => 0,
    ];
    return $map[$status] ?? 0;
}
