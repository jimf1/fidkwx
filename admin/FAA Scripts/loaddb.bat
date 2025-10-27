cls
@echo To take database updates, 
@echo 1. Unload the FAA airport database to a work directory
@echo 2. Be sure the "..\FAA Data" directory structure (from .zip file) exists
@echo 3. Run this file
pause
set mysqldir=C:\laragon\bin\mysql\mysql-8.0.30-winx64\bin\
set serverparms=--host=localhost --user=wxuser --password=weather 
set dbinstance=fidkwx

"%mysqldir%mysql" %serverparms% %dbinstance% < "Create ARPT Table.sql"
"%mysqldir%mysqlimport"  --local --ignore-lines=1 %serverparms% %dbinstance%  "..\FAA Data\ARPT.txt"

pause