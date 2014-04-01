#!/bin/bash

DIRNAME=$1
BASENAME=$2
PATH=$3

/usr/bin/avconv -y -i $PATH -vcodec libx264 $DIRNAME/$BASENAME.mp4 >/var/log/nginx/ffmpeg-$BASENAME.log 2>&1