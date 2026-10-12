@echo off
set "GIT=%LOCALAPPDATA%\MinGit\cmd\git.exe"
del tests\setup_git.ps1 2>nul
del tests\init_repo.ps1 2>nul
"%GIT%" add .
"%GIT%" commit -m "Initial commit: TaskMate - Aplikasi Pengelolaan Tugas Mahasiswa"
"%GIT%" log -n 1
