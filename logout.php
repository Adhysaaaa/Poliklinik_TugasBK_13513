<?php
session_start();

// Hapus semua variabel sesi
session_unset();

// Hancurkan sesi
session_destroy();

// Arahkan kembali ke halaman home.html
header("Location: home.html");
exit;