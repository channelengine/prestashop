del .\export\channelengine-prestashop.zip
xcopy /r /d /i /y /s /exclude:.xcopyignore . %TEMP%\channelengine-prestashop\channelengine
7z a -r .\export\channelengine-prestashop.zip -w %TEMP%\channelengine-prestashop\channelengine
rd /s /q %TEMP%\channelengine-prestashop\