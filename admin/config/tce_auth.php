<?php
// FILE: admin/config/tce_auth.php

// Deskripsi Baru:
// 1 = Pengguna Terdaftar (Pelamar)
// 2 = System Administrator
define('K_AUTH_ADMINISTRATOR', 2);
define('K_AUTH_REGISTERED', 1);

// Level minimum untuk akses halaman utama admin adalah Admin
define('K_AUTH_INDEX', K_AUTH_ADMINISTRATOR);

// Semua fitur di bawah ini sekarang HANYA bisa diakses oleh ADMIN (Level 2)
define('K_AUTH_ADMIN_USERS', K_AUTH_ADMINISTRATOR);
define('K_AUTH_DELETE_USERS', K_AUTH_ADMINISTRATOR);
define('K_AUTH_EXPORT_USERS', K_AUTH_ADMINISTRATOR);
define('K_AUTH_IMPORT_USERS', K_AUTH_ADMINISTRATOR);
define('K_AUTH_ADMIN_GROUPS', K_AUTH_ADMINISTRATOR);
define('K_AUTH_DELETE_GROUPS', K_AUTH_ADMINISTRATOR);
define('K_AUTH_MOVE_GROUPS', K_AUTH_ADMINISTRATOR);
define('K_AUTH_ADMIN_TCECODE', K_AUTH_ADMINISTRATOR);
define('K_AUTH_ADMIN_MODULES', K_AUTH_ADMINISTRATOR);
define('K_AUTH_ADMIN_SUBJECTS', K_AUTH_ADMINISTRATOR);
define('K_AUTH_ADMIN_QUESTIONS', K_AUTH_ADMINISTRATOR);
define('K_AUTH_ADMIN_ANSWERS', K_AUTH_ADMINISTRATOR);
define('K_AUTH_ADMIN_TESTS', K_AUTH_ADMINISTRATOR);
define('K_AUTH_ADMIN_ONLINE_USERS', K_AUTH_ADMINISTRATOR);
define('K_AUTH_ADMIN_UPLOAD_IMAGES', K_AUTH_ADMINISTRATOR);
define('K_AUTH_ADMIN_RATING', K_AUTH_ADMINISTRATOR);
define('K_AUTH_ADMIN_RESULTS', K_AUTH_ADMINISTRATOR);
define('K_AUTH_ADMIN_IMPORT', K_AUTH_ADMINISTRATOR);
define('K_AUTH_ADMIN_OMR_IMPORT', K_AUTH_ADMINISTRATOR);
define('K_AUTH_BACKUP', K_AUTH_ADMINISTRATOR);
define('K_AUTH_ADMIN_FILEMANAGER', K_AUTH_ADMINISTRATOR);
define('K_AUTH_ADMIN_DIRS', K_AUTH_ADMINISTRATOR);
define('K_AUTH_DELETE_MEDIAFILE', K_AUTH_ADMINISTRATOR);
define('K_AUTH_RENAME_MEDIAFILE', K_AUTH_ADMINISTRATOR);
define('K_AUTH_ADMIN_SSLCERT', K_AUTH_ADMINISTRATOR);

// Halaman info bisa diakses oleh siapa saja yang sudah login
define('K_AUTH_ADMIN_INFO', K_AUTH_REGISTERED);

// Pengaturan SSL (biarkan default)
define('K_AUTH_SSL_LEVEL', false);
define('K_AUTH_SSLIDS', '');

//============================================================+
// END OF FILE
//============================================================+