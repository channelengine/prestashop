setlocal
set PRESTA_PLUGIN=C:\projects\channelengine-prestashop
set PRESTA_ROOT=C:\projects\prestashop

echo %PRESTA_PLUGIN%
echo %PRESTA_ROOT%

mklink /D "%PRESTA_ROOT%\modules\channelengine" "%PRESTA_PLUGIN%"