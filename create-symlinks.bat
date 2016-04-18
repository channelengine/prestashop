setlocal
set PRESTA_PLUGIN=C:\projects\channelengine-prestashop
set PRESTA_ROOT=C:\projects\prestashop


mklink /D "%PRESTA_ROOT%\modules\channelengine" "%PRESTA_PLUGIN%"
