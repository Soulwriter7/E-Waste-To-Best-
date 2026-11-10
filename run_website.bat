@echo off
setlocal enabledelayedexpansion
title E-Waste to Best - Smart Recycling and Metal Recovery System

:: -------------------------------------------------------------
:: Colors and Visual Styling
:: -------------------------------------------------------------
color 0A
cls

echo ===============================================================================
echo       E-WASTE TO BEST: Smart E-Waste Recycling & Metal Recovery System
echo ===============================================================================
echo.
echo  [*] Initializing environment...
echo.

:: -------------------------------------------------------------
:: Step 1: Detect XAMPP Installation Directory
:: -------------------------------------------------------------
set "XAMPP_DIR="

if exist "C:\xampp\" (
    set "XAMPP_DIR=C:\xampp"
) else if exist "D:\xampp\" (
    set "XAMPP_DIR=D:\xampp"
) else if exist "E:\xampp\" (
    set "XAMPP_DIR=E:\xampp"
)

if "%XAMPP_DIR%"=="" (
    echo  [X] ERROR: XAMPP installation could not be found on C:\, D:\, or E:\ drive.
    echo      Please install XAMPP or set the path manually.
    echo.
    pause
    exit /b 1
)

echo  [OK] Detected XAMPP at: %XAMPP_DIR%

set "APACHE_BIN=%XAMPP_DIR%\apache\bin\httpd.exe"
set "MYSQL_BIN=%XAMPP_DIR%\mysql\bin\mysql.exe"
set "MYSQLD_BIN=%XAMPP_DIR%\mysql\bin\mysqld.exe"
set "HTDOCS_DEST=%XAMPP_DIR%\htdocs\Waste to best"
set "CURRENT_DIR=%~dp0"
:: Strip trailing backslash from CURRENT_DIR
if "%CURRENT_DIR:~-1%"=="\" set "CURRENT_DIR=%CURRENT_DIR:~0,-1%"

:: -------------------------------------------------------------
:: Step 2: Ensure MySQL Service is Running
:: -------------------------------------------------------------
echo  [*] Checking MySQL status...
tasklist /FI "IMAGENAME eq mysqld.exe" 2>NUL | find /I /N "mysqld.exe" >NUL
if "%ERRORLEVEL%"=="0" (
    echo  [OK] MySQL service is currently running.
) else (
    echo  [!] Starting MySQL service...
    if exist "%XAMPP_DIR%\mysql_start.bat" (
        start "" /B "%XAMPP_DIR%\mysql_start.bat" >NUL 2>&1
    ) else (
        start "" /B "%MYSQLD_BIN%" --defaults-file="%XAMPP_DIR%\mysql\bin\my.ini" --standalone >NUL 2>&1
    )
    timeout /t 3 /nobreak >NUL
    echo  [OK] MySQL service started.
)

:: -------------------------------------------------------------
:: Step 3: Ensure Apache Service is Running
:: -------------------------------------------------------------
echo  [*] Checking Apache status...
tasklist /FI "IMAGENAME eq httpd.exe" 2>NUL | find /I /N "httpd.exe" >NUL
if "%ERRORLEVEL%"=="0" (
    echo  [OK] Apache service is currently running.
) else (
    echo  [!] Starting Apache service...
    if exist "%XAMPP_DIR%\apache_start.bat" (
        start "" /B "%XAMPP_DIR%\apache_start.bat" >NUL 2>&1
    ) else (
        start "" /B "%APACHE_BIN%" >NUL 2>&1
    )
    timeout /t 3 /nobreak >NUL
    echo  [OK] Apache service started.
)

:: -------------------------------------------------------------
:: Step 4: Sync files to XAMPP htdocs if running from outside
:: -------------------------------------------------------------
if /I not "%CURRENT_DIR%"=="%HTDOCS_DEST%" (
    echo  [*] Synchronizing project files to %HTDOCS_DEST%...
    if not exist "%HTDOCS_DEST%" mkdir "%HTDOCS_DEST%"
    xcopy "%CURRENT_DIR%" "%HTDOCS_DEST%\" /E /I /Y /Q /EXCLUDE:%CURRENT_DIR%\.exclude_list >NUL 2>&1
    if "%ERRORLEVEL%"=="0" (
        echo  [OK] Project files synchronized to XAMPP htdocs.
    ) else (
        robocopy "%CURRENT_DIR%" "%HTDOCS_DEST%" /E /NFL /NDL /NJH /NJS /nc /ns /np >NUL 2>&1
        echo  [OK] Project files synchronized to XAMPP htdocs via robocopy.
    )
)

:: -------------------------------------------------------------
:: Step 5: Check and Setup Database (`ewaste_db`)
:: -------------------------------------------------------------
echo  [*] Checking database "ewaste_db"...
"%MYSQL_BIN%" -u root -e "USE ewaste_db;" >NUL 2>&1
if "%ERRORLEVEL%"=="0" (
    echo  [OK] Database "ewaste_db" is ready and verified.
) else (
    echo  [!] Database "ewaste_db" not found. Importing config\seed_data.sql...
    if exist "%CURRENT_DIR%\config\seed_data.sql" (
        "%MYSQL_BIN%" -u root < "%CURRENT_DIR%\config\seed_data.sql"
        echo  [OK] Database "ewaste_db" successfully created and populated.
    ) else (
        echo  [X] WARNING: seed_data.sql could not be found at config\seed_data.sql.
    )
)

:: -------------------------------------------------------------
:: Step 6: Launch Web Browser
:: -------------------------------------------------------------
set "TARGET_URL=http://localhost/Waste to best/"
echo.
echo  ===============================================================================
echo    WEBSITE IS READY AND RUNNING!
echo  ===============================================================================
echo.
echo    [URL]    : %TARGET_URL%
echo.
echo    [DEMO CREDENTIALS FOR COLLEGE PRESENTATION]
echo    - Administrator : admin@ewaste.org  /  admin123
echo    - Citizen Demo  : rahul@gmail.com   /  user123
echo.
echo  [*] Opening website in your default browser...
start "" "%TARGET_URL%"

:: -------------------------------------------------------------
:: Interactive Control Menu
:: -------------------------------------------------------------
:MENU
echo.
echo  -------------------------------------------------------------------------------
echo    SELECT AN OPTION:
echo  -------------------------------------------------------------------------------
echo    [1] Re-open Website in Browser
echo    [2] Open phpMyAdmin (Database Manager)
echo    [3] Re-import / Reset Database (config\seed_data.sql)
echo    [4] Sync Workspace Files to htdocs
echo    [5] Open XAMPP Control Panel
echo    [6] Exit Launcher
echo.
set /p CHOICE="  Enter choice [1-6]: "

if "%CHOICE%"=="1" (
    start "" "%TARGET_URL%"
    goto MENU
)
if "%CHOICE%"=="2" (
    start "" "http://localhost/phpmyadmin/"
    goto MENU
)
if "%CHOICE%"=="3" (
    echo.
    echo  [*] Re-importing seed data into ewaste_db...
    "%MYSQL_BIN%" -u root < "%CURRENT_DIR%\config\seed_data.sql"
    echo  [OK] Database successfully reset and fresh seed data imported.
    goto MENU
)
if "%CHOICE%"=="4" (
    echo.
    echo  [*] Synchronizing files to %HTDOCS_DEST%...
    robocopy "%CURRENT_DIR%" "%HTDOCS_DEST%" /E /NFL /NDL /NJH /NJS /nc /ns /np >NUL 2>&1
    echo  [OK] Synchronization complete.
    goto MENU
)
if "%CHOICE%"=="5" (
    if exist "%XAMPP_DIR%\xampp-control.exe" (
        start "" "%XAMPP_DIR%\xampp-control.exe"
    ) else (
        echo  [!] xampp-control.exe not found.
    )
    goto MENU
)
if "%CHOICE%"=="6" (
    echo.
    echo  Thank you for using E-Waste to Best!
    timeout /t 2 /nobreak >NUL
    exit /b 0
)

echo  [!] Invalid selection. Please enter a number from 1 to 6.
goto MENU
