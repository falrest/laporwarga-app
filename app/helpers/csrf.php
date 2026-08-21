<?php
/**
 * CSRF protection.
 * Setiap form/POST/AJAX wajib menyertakan token ini (section 26).
 */

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token()) . '">';
}

/**
 * Validasi token dari POST body ATAU header X-CSRF-Token (untuk request AJAX/JSON).
 */
function csrf_verify(?string $token = null): bool
{
    if ($token === null) {
        $token = $_POST['csrf_token']
            ?? $_SERVER['HTTP_X_CSRF_TOKEN']
            ?? null;
    }

    if (!$token || empty($_SESSION['csrf_token'])) {
        return false;
    }

    return hash_equals($_SESSION['csrf_token'], $token);
}

/** Panggil di awal handler POST API. Menghentikan request jika token tidak valid. */
function csrf_require(): void
{
    $body = null;
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

    if (str_contains($contentType, 'application/json')) {
        $body = json_input();
        $token = $body['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);
    } else {
        $token = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);
    }

    if (!csrf_verify($token)) {
        json_error('Token keamanan tidak valid. Silakan muat ulang halaman.', 419);
    }
}
