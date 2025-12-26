#!/bin/bash

INPUT="input.mov"
OUTPUT_DIR="hls"

mkdir -p "$OUTPUT_DIR"

for RES in 360 480; do
  mkdir -p "$OUTPUT_DIR/$RES"
  ffmpeg -y -i "$INPUT" \
    -vf "scale=-2:$RES" \
    -c:v libx264 -preset slow -crf 23 \
    -c:a aac -b:a 128k \
    -hls_time 10 \
    -hls_playlist_type vod \
    -hls_segment_filename "$OUTPUT_DIR/$RES/segment_%03d.ts" \
    "$OUTPUT_DIR/$RES/index.m3u8"
done

# Master playlist
cat <<EOL > "$OUTPUT_DIR/master.m3u8"
#EXTM3U
#EXT-X-STREAM-INF:BANDWIDTH=800000,RESOLUTION=640x360
360/index.m3u8
#EXT-X-STREAM-INF:BANDWIDTH=1200000,RESOLUTION=854x480
480/index.m3u8
#EXT-X-STREAM-INF:BANDWIDTH=2400000,RESOLUTION=1280x720
720/index.m3u8
#EXT-X-STREAM-INF:BANDWIDTH=4800000,RESOLUTION=1920x1080
1080/index.m3u8
EOL

echo "HLS generation complete. Master playlist: ${OUTPUT_DIR}/master.m3u8"
