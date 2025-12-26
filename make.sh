#!/bin/bash

# HLS generation script for input.mov
# Creates multiple renditions in a single command: 360p, 480p, 720p, 1080p

INPUT="input.mov"
OUTPUT_DIR="hls"

mkdir -p "$OUTPUT_DIR"

ffmpeg -y -i "$INPUT" \
  -filter_complex \
    "[0:v]scale=if(gt(ih,360),-2,360)[v360p];[0:v]scale=if(gt(ih,480),-2,480)[v480p];[0:v]scale=if(gt(ih,720),-2,720)[v720p];[0:v]scale=if(gt(ih,1080),-2,1080)[v1080p]" \
  -map [v360p] -map 0:a:0 \
    -c:v:0 libx264 -b:v:0 800k -maxrate 960k -bufsize 1600k \
    -c:a:0 aac -b:a:0 128k \
  -map [v480p] -map 0:a:0 \
    -c:v:1 libx264 -b:v:1 1200k -maxrate 1440k -bufsize 2400k \
    -c:a:1 aac -b:a:1 128k \
  -map [v720p] -map 0:a:0 \
    -c:v:2 libx264 -b:v:2 2400k -maxrate 2880k -bufsize 4800k \
    -c:a:2 aac -b:a:2 128k \
  -map [v1080p] -map 0:a:0 \
    -c:v:3 libx264 -b:v:3 4800k -maxrate 5760k -bufsize 9600k \
    -c:a:3 aac -b:a:3 128k \
  -f hls \
  -hls_time 10 \
  -hls_playlist_type vod \
  -hls_segment_filename "${OUTPUT_DIR}/%v/segment_%03d.ts" \
  -master_pl_name master.m3u8 \
  -var_stream_map "v:0,a:0 v:1,a:1 v:2,a:2 v:3,a:3" \
  "${OUTPUT_DIR}/%v/index.m3u8"

echo "HLS generation complete. Master playlist: ${OUTPUT_DIR}/master.m3u8"