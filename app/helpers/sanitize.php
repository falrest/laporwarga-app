<?php
/**
 * Helper sanitasi & validasi input backend.
 * Validasi frontend hanya untuk UX — backend WAJIB tetap memvalidasi ulang.
 */

function s(string $value): string
{
    return trim($value);
}

function out(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function is_valid_email(string $email): bool
{
    return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
}

function is_valid_nik(string $nik): bool
{
    return (bool) preg_match('/^\d{16}$/', $nik);
}

function is_valid_phone(string $phone): bool
{
    return (bool) preg_match('/^08\d{8,12}$/', $phone);
}

function is_valid_password(string $password): bool
{
    return strlen($password) >= 8;
}

/** Generate nomor tiket unik: LW-YYYY-NNNNNN (section 12). */
function generate_ticket_number(PDO $pdo): string
{
    $year = date('Y');
    do {
        $random = random_int(1, 999999);
        $ticket = sprintf('LW-%s-%06d', $year, $random);
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM reports WHERE ticket_number = ?');
        $stmt->execute([$ticket]);
        $exists = (int) $stmt->fetchColumn() > 0;
    } while ($exists);

    return $ticket;
}

/** Mask NIK: 3271********9012 (section 17). */
function mask_nik(?string $nik): string
{
    if (!$nik || strlen($nik) !== 16) {
        return '-';
    }
    return substr($nik, 0, 4) . str_repeat('*', 8) . substr($nik, -4);
}

/** Mask nomor HP: 0856****2039 (section 17). */
function mask_phone(?string $phone): string
{
    if (!$phone || strlen($phone) < 8) {
        return $phone ?? '-';
    }
    return substr($phone, 0, 4) . str_repeat('*', 4) . substr($phone, -4);
}
