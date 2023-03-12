# ClubPenguin-Flash

How to run
1) Download XAMPP (https://www.apachefriends.org/download.html)
2) Install and open XAMPP Control Panel
3) Start _**Apache**_ and _**MySQL**_ from actions
4) Click _**Explorer**_ button and go to _**../xampp/php**_ directory and open php.ini file, then uncomment line ```extension=php_sockets.dll``` and save it
5) Go to _**../xampp/htdocs**_ directory and paste everything from this repo htdocs folder
6) Go to browser and type http://localhost/phpmyadmin/
7) Click on 'Databases' tab and create new db called _**kitsune**_ and press button Create
8) Click on 'Import' tab and press Choose file and choose the file _**../xampp/htdocs/Kitsune/Kitsune.sql**_, then scroll down and press Go button
9) Open XAMPP terminal and navigate to  _**../xampp/htdocs/Kitsune/Kitsune.sql**_ directory using ```cd``` command
10) Now run command ```php run.php``` 
11) After that should see Kitsune logo
```
 _   ___ _                        
| | / (_) |                       
| |/ / _| |_ ___ _   _ _ __   ___ 
|    \| | __/ __| | | | '_ \ / _ \
| |\  \ | |_\__ \ |_| | | | |  __/
\_| \_/_|\__|___/\__,_|_| |_|\___|
```
11) Go to browser and type http://localhost/play/load.swf
