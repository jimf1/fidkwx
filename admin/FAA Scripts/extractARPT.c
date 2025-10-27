/*
Read FAA airport data file and generate tab delimited file suitable for loading ARPT database 
for FIDK weather site.

Per FAA definition in apt_rf.txt file, offsets are as follows:
Logical record length: 1529
												  Length   Offset
	Record Type Indicator							0003	00001
	Location Identifier								0004	00028
	Official Facility Name							0050	00134
	Airport Reference Point Latitude (Formatted)	0015	00524
	Airport Reference Point Latitude (Seconds)		0012	00539
	Airport Reference Point Longitude (Formatted)	0015	00551
	Airport Reference Point Longitude (Seconds)		0012	00566
	ICAO Identifier									0007	01211
	City											0040	00094
	State											0002	00049
	County State (Used for international)			0002	00092
	
File name for data is apt.txt

Output is tab delimited file with headers.  Data elements are:
	FAA identifier,
	ICAO identifier, 
	Facility Name, in the format "NAME [City State]"
	Degrees Latitude in decimal format
	Degrees Longitude in decimal format
	
Input: STDIN
Output: STDOUT

Normally, batch file will call program like this:
	extractAPT <apt.txt >aptout.txt

*/


#include <stdio.h>
#include <assert.h>
#include <stdarg.h>
#include <unistd.h>
#include <string.h>
#include <stdlib.h>
#include <ctype.h>

extern int read_fixed_length_line(FILE *fp, char *buffer, int linelen);

/* Read line of fixed length linelen characters followed by newline. */
/* Buffer must have room for trailing NUL (newline is not included). */
/* Returns length of line that was read (excluding newline), or EOF. */

int read_fixed_length_line(FILE *fp, char *buffer, int linelen)
{
    int count = 0;
    int c;
    assert(fp != 0 && buffer != 0 && linelen > 0);
    while (count < linelen)
    {
        if ((c = getc(fp)) == EOF || c == '\n')
            break;
        buffer[count++] = c;
    }
    buffer[count] = '\0';
    if (c != EOF && c != '\n')
    {
        /* Gobble overlength characters on line */
        int x;
        while ((x = getc(fp)) != EOF && x != '\n')
            count++;
    }
    return((c == EOF) ? EOF : count);
}

char *rtrim(char *str)
// Trim rightmost spaces from a string
// Return pointer to original string
{
  char *end;

  // Trim trailing space
  end = str + strlen(str) - 1;
  while(end > str && isspace((unsigned char)*end)) end--;

  // Write new null terminator
  *(end+1) = 0;

  return str;
}



int main(void)
{
    enum { MAXLINELEN = 1529 };
    int actlen;
    char line[1560];
    int lineno = 0;
    memset(line, '\0', sizeof(line));
	char recordType[4];
	char icaoID[5];
	char faaID[5];
	char facilityName[51];
	char arpFormattedLat[16];
	char arpSecondsLat[13];
	char arpFormattedLon[16];
	char arpSecondsLon[13];
	char city[41];
	char stateCode[3];
	char countyStateCode[3]; //Used for international airports
	char displayStateCode[3]; //Used to populate airport name, either stateCode or countyStateCode
	double arpDegreesLat;
	double arpDegreesLon;
	char *latDirection;
	char *lonDirection;
	
	//Print Header line
	
	printf ("FAA_HOST_ID\tICAO\tNAME\tWGS_DLAT\tWGS_DLONG \n");

    while ((actlen = read_fixed_length_line(stdin, line, MAXLINELEN)) != EOF)
    {
        lineno++;
		//initialize vars
		memset(recordType, '\0', sizeof(recordType));
		memset(icaoID, '\0', sizeof(icaoID));
		memset(faaID, '\0', sizeof(faaID));
		memset(facilityName, '\0', sizeof(facilityName));
		memset(arpFormattedLat, '\0', sizeof(arpFormattedLat));
		memset(arpFormattedLon, '\0', sizeof(arpFormattedLon));
		memset(arpSecondsLat, '\0', sizeof(arpSecondsLat));
		memset(arpSecondsLon, '\0', sizeof(arpSecondsLon));
		memset(city, '\0', sizeof(city));
		memset(stateCode, '\0', sizeof(stateCode));
		memset(countyStateCode, '\0', sizeof(countyStateCode));
		
		//Determine record type ... we want APT
		strncpy(recordType, line, 3);
		
		
		
        if (strcmp(recordType, "APT") == 0)
        {
			strncpy(faaID, line+27, 4);
			strncpy(icaoID, line+1210, 4);
			strncpy(facilityName, line+133, 50);
			strncpy(arpFormattedLat, line+523, 15);
			strncpy(arpSecondsLat, line+538, 12);
			strncpy(arpFormattedLon, line+550, 15);
			strncpy(arpSecondsLon, line+565, 12);
			strncpy(city, line+93, 40);
			strncpy(stateCode, line+48, 2);
			strncpy(countyStateCode, line+91, 2);
			
			/*
            printf("%2d:R: length %2d <<%s>>\n", lineno, actlen, line);
			printf("Record type is: %s\n", recordType);
			printf("FAA ID: %s\n", faaID);
			printf("ICAO ID: %s\n", icaoID);
			
			printf("Facility Name: %s\n", facilityName);
			printf("ARP Formatted Lat: %s\n", arpFormattedLat);
			printf("ARP Seconds Lat: %s\n", arpSecondsLat);
			printf("ARP Formatted Lon: %s\n", arpFormattedLon);
			printf("ARP Seconds Lon: %s\n", arpSecondsLon);
			*/
						
			//Calculate degrees latitude from seconds latitude
			arpDegreesLat = strtod(arpSecondsLat, &latDirection) / 3600;
			if (strcmp(latDirection, "S") == 0)
				arpDegreesLat = -arpDegreesLat;
			/*
			printf ("ARP Degrees Lat = %f\n", arpDegreesLat);
			printf ("ARP Degrees Lat Direction = %s\n", latDirection);
			*/
			
			//Calculate degrees longitude from seconds longitude
			arpDegreesLon = strtod(arpSecondsLon, &lonDirection) / 3600;
			if (strcmp(lonDirection, "W") == 0)
				arpDegreesLon = -arpDegreesLon;
			/*
			printf ("ARP Degrees Lon = %f\n", arpDegreesLon);
			printf ("ARP Degrees Lon Direction = %s\n", lonDirection);
			*/
			
			//Determine state code to display
			if (strcmp(stateCode,"  ") == 0)
				strcpy(displayStateCode,countyStateCode);  //A non-US airport
			else
				strcpy(displayStateCode,stateCode);  //A US airport
			
			//Fix the leading "K" that's sometimes missing in FAA database (incompatible with ADDS)
			//Look for a 3 character faaID that's in the US
			if (strchr(faaID, ' ') == faaID + 3 && strcmp(icaoID,"    ") == 0 && strcmp(stateCode,"  ") != 0)
			{
				strcpy(icaoID, "K");
				strcpy(icaoID+1, faaID);
			}
			
			printf ("%s\t%s\t%s [%s %s]\t %f\t%f\n", faaID, icaoID, rtrim(facilityName), rtrim(city), displayStateCode, arpDegreesLat,arpDegreesLon);
		}
        assert(line[MAXLINELEN-0] == '\0');
        assert(line[MAXLINELEN+1] == '\0');
    }
    return 0;
}
