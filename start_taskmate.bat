@echo off
title TaskMate - Server Pengelolaan Tugas Mahasiswa
color 0b

echo ========================================================
echo    TaskMate - Aplikasi Pengelolaan Tugas Mahasiswa
echo ========================================================
echo.
echo Sedang menyiapkan server dan membuka aplikasi di Google Chrome...
echo URL: http://localhost:8000
echo.

:: Buka Google Chrome ke alamat aplikasi
start chrome http://localhost:8000 || start http://localhost:8000

:: Jalankan server PHP menggunakan PHP XAMPP atau PHP bawaan sistem
if exist "C:\xampp\php\php.exe" (
    echo Menjalankan PHP Server via C:\xampp\php\php.exe...
    "C:\xampp\php\php.exe" -S 127.0.0.1:8000
) else (
    echo Menjalankan PHP Server via PATH...
    php -S 127.0.0.1:8000
)

pause
