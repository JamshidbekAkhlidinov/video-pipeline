<?php
require __DIR__ . '/vendor/autoload.php';

use ustadev\videopipeline\video\VideoProcessor;


$inputFile = __DIR__ . '/input.mov';   // test uchun input fayl
$hlsDir = __DIR__ . '/hls-videos'; // HLS fayllari saqlanadigan papka

if (!is_dir($hlsDir)) {
    mkdir($hlsDir, 0755, true);
}

$processor = new VideoProcessor($inputFile);

echo "Generating HLS renditions...\n";

$hls = $processor
    ->generateHls($hlsDir, [480,720])
    ->setMasterFileName("ustadev")
    ->setDuration(30);

$masterPlaylist = $hls->generate(function($progress) {
    echo "\rHLS progress: {$progress}%";
});

echo "\nHLS master playlist: $masterPlaylist\n";

echo "HLS renditions created in: $hlsDir\n";

echo "You can play master.m3u8 in VLC or ffplay\n";

