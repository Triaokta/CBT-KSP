<?php
/**
 * file: tce_punish_user.php
 * version: 9.0 (Standard mysqli functions)
 */

// Memanggil file konfigurasi yang akan memuat semua kebutuhan
require_once('../../shared/config/tce_config.php');

// Beritahu browser bahwa jawaban dari file ini PASTI dalam format JSON.
header('Content-Type: application/json');

// Ambil user_id dari URL yang dikirim oleh JavaScript
if (isset($_REQUEST['user_id']) && is_numeric($_REQUEST['user_id']) && ($_REQUEST['user_id'] > 0)) {
    
    $user_id = intval($_REQUEST['user_id']);

    // Pastikan variabel koneksi database ($db) ada.
    if (!isset($db) || !$db) {
         echo json_encode(['status' => 'error', 'message' => 'Database connection object not found.']);
         die();
    }

    // Mengamankan user_id menggunakan fungsi standar PHP yang benar
    $safe_user_id = $db->real_escape_string($user_id);

    // 1. Ubah level user menjadi 0
    $table_users = K_TABLE_USERS;
    $sql_update = "UPDATE `$table_users` SET `user_level` = 0 WHERE `user_id` = '$safe_user_id'";
    
    if ($r_update = $db->query($sql_update)) {
        
        // 2. Hapus SEMUA sesi milik user ini dari database
        $table_sessions = K_TABLE_SESSIONS;
        $sql_delete = "DELETE FROM `$table_sessions` WHERE `cpsession_user_id` = '$safe_user_id'";
        $db->query($sql_delete);

        // 3. Hancurkan sesi browser saat ini
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        session_unset();
        session_destroy();
        
        // 4. Kirim laporan sukses
        echo json_encode(['status' => 'success', 'message' => 'User punished, level set to 0, and all sessions cleared.']);
        die();

    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to execute update query: '.$db->get_error_message()]);
        die();
    }

} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid or missing user_id.']);
    die();
}