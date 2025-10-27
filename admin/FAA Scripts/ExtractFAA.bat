cls
@echo To take database updates, 
@echo 1. Unload the FAA airport database to a work directory
@echo 2. Be sure the ..\FAA Data directory structure (from .zip file) exists
@echo 3. Run this file.  This will make ARPT.txt from APT.txt
pause

extractARPT.exe < "..\FAA Data\APT.txt" > "..\FAA Data\ARPT.txt"

@echo You should now have ..\FAA Data\ARPT.txt
@echo Move on to loaddb.bat or fidkloaddb.bat (for the server)