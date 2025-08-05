@echo off
echo ========================================
echo   STARTING WEBSOCKET NOTIFICATION SERVER
echo ========================================
echo.
echo Starting Simple WebSocket Server on port 8080...
echo Press Ctrl+C to stop the server
echo.

REM Change to project directory
cd /d "C:\laragon\www\projectPegawai-master"

REM Start PHP WebSocket server
C:\laragon\bin\php\php-8.1.10-Win32-vs16-x64\php.exe simple-websocket-server.php

pause
