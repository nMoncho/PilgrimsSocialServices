@echo off

SET "CURR_DIR=%cd%"

copy "%CURR_DIR%\common.js" "C:\Program Files\Construct 2\exporters\html5\plugins\PilgrimsSocialServices\common.js"
copy "%CURR_DIR%\runtime.js" "C:\Program Files\Construct 2\exporters\html5\plugins\PilgrimsSocialServices\runtime.js"
copy "%CURR_DIR%\edittime.js" "C:\Program Files\Construct 2\exporters\html5\plugins\PilgrimsSocialServices\edittime.js"