cls
@echo To take database updates, 
@echo 1. Unload the FAA airport database to a work directory
@echo 2. Be sure the "..\FAA Data" directory structure (from .zip file) exists
@echo 3. Run this file
pause
set mysqldir=C:\laragon\bin\mysql\mysql-8.0.30-winx64\bin\
set serverparms=--host=mysql.server268.com --port=3308 --user=fidk --password=DaterTater123 --local-infile
set dbinstance=fidk_wx

"%mysqldir%mysql" %serverparms% %dbinstance% < "Create ARPT Table.sql"
"%mysqldir%mysql" %serverparms% %dbinstance% < "Load ARPT Table.sql"
pause