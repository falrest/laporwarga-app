<?php
/**
 * Helper untuk response JSON yang konsisten (lihat section 29 spesifikasi).
 */

function json_success(string $message, array $data = [], int $code = 200): void
{
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => $message,
        'data'    => $data,
    ]);
    exit;
}

function json_error(string $message, int $code = 400, array $errors = []): void
{
    http_response_code($code);
    header('Content-Type: application/json');
    $payload = [
        'success' => false,
        'message' => $message,
    ];
    if (!empty($errors)) {
        $payload['errors'] = $errors;
    }
    echo json_encode($payload);
    exit;
}

/** Ambil body JSON request sebagai array asosiatif. */
function json_input(): array
{
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}
