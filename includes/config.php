<?php
    
// Konfigurasi Database
$host = "localhost";
$username = "root";
$password = "";
$database = "tugasloginregister";

// Membuat koneksi
$conn = mysqli_connect($host, $username, $password, $database);

// Cek koneksi
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>