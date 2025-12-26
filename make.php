<?php
require __DIR__ . '/vendor/autoload.php';

use ustadev\videopipeline\video\VideoProcessor;

// ========================================
// CONFIGURATION
// ========================================
$inputFile = __DIR__ . '/input.mov';   // test uchun input fayl
$outputFile = __DIR__ . '/output_720.mp4'; // resized output
$optimizedFile = __DIR__ . '/output_optimized.mp4'; // optimized output
$thumbnailFile = __DIR__ . '/thumbnail.jpg'; // thumbnail output
$hlsDir = __DIR__ . '/hls'; // HLS fayllari saqlanadigan papka

// Make sure HLS directory exists
if (!is_dir($hlsDir)) {
    mkdir($hlsDir, 0777, true);
}

// ========================================
// INITIALIZE PROCESSOR
// ========================================
$processor = new VideoProcessor($inputFile);

// // ========================================
// RESIZE VIDEO
// ========================================
echo "Resizing video to max 720p...\n";

$resize = $processor->resize(720)->setCrf(20);
$processor->process($resize, $outputFile, function($progress) {
    echo "\rResize progress: {$progress}%";
});
echo "\nResized video saved to: $outputFile\n\n";

// ========================================
// OPTIMIZE VIDEO
// ========================================
echo "Optimizing video...\n";

$optimize = $processor->optimize()->setAudioBitrate(128);
$processor->process($optimize, $optimizedFile, function($progress) {
    echo "\rOptimize progress: {$progress}%";
});
echo "\nOptimized video saved to: $optimizedFile\n\n";

// ========================================
// GENERATE THUMBNAIL
// ========================================
echo "Generating thumbnail...\n";

$thumbnail = $processor->thumbnail()->setTimestamp(1.0)->setSize(320, 180);
$processor->process($thumbnail, $thumbnailFile);
echo "Thumbnail saved to: $thumbnailFile\n\n";

// ========================================
// HLS GENERATION
// ========================================
echo "Generating HLS renditions...\n";

$hls = $processor->generateHls($hlsDir, [360, 480]);
$masterPlaylist = $hls->generate(function($progress) {
    echo "\rHLS progress: {$progress}%";
});
echo "\nHLS master playlist: $masterPlaylist\n";
echo "HLS renditions created in: $hlsDir\n";
echo "You can play master.m3u8 in VLC or ffplay\n";

