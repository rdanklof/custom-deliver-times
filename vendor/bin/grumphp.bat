@ECHO OFF
setlocal DISABLEDELAYEDEXPANSION
SET BIN_TARGET=%~dp0/../phpro/grumphp/bin/grumphp
php "%BIN_TARGET%" %*
