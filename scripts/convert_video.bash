#!/bin/bash

DIRNAME=$1
BASENAME=$2
PATH=$3
LOGFILE="/var/log/nginx/ffmpeg-$BASENAME.log"

TODAY=$(date +"%Y%m%d %H%M%S")

echo "$TODAY *** Start conversion ***" > $LOGFILE
echo "$TODAY - Convert file $PATH to $DIRNAME/$BASENAME.mp4" >> $LOGFILE
/usr/bin/avconv -y -i $PATH -vcodec libx264 $DIRNAME/$BASENAME.mp4 >>$LOGFILE 2>&1

RESULT=$?

TODAY=$(date +"%Y%m%d %H%M%S")
if [ $RESULT -ne 0 ]; then
    echo "$TODAY - ERROR - File $DIRNAME/$BASENAME.mp4 conversion FAILED!" >> $LOGFILE
else
    echo "$TODAY - File $DIRNAME/$BASENAME.mp4 conversion SUCCESS!" >> $LOGFILE
fi