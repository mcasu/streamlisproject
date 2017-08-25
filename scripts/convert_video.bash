#!/bin/bash

SOURCE=$1
DEST=$2
BASENAME=$3

LOGFILE="/var/log/nginx/avconv-convert-$BASENAME.log"

TODAY=$(/bin/date +"%Y%m%d-%H:%M:%S")
echo "$TODAY - *** Start conversion ***" > $LOGFILE
echo "$TODAY - Convert file $SOURCE to $DEST" >> $LOGFILE
/usr/bin/avconv -y -i $SOURCE -vcodec libx264 -c:a aac -b:a 64k -strict experimental $DEST >>$LOGFILE 2>&1

RESULT=$?

TODAY=$(/bin/date +"%Y%m%d-%H:%M:%S")
if [ $RESULT -ne 0 ]; then
    echo "$TODAY - ERROR - File $DEST conversion FAILED!" >> $LOGFILE
    echo "Conversione del file $SOURCE nel formato MP4 fallita! In allegato il file di log." | mail -s "AVCONV ERROR - [$BASENAME] CONVERSIONE FALLITA!" -r "StreamLIS<noreply@streamlis.it>" -a $LOGFILE info@streamlis.it
else
    echo "$TODAY - File $DEST conversion SUCCESS!" >> $LOGFILE
fi