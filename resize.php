<?php
require __DIR__ . '/vendor/autoload.php';

use ustadev\videopipeline\video\VideoProcessor;


$inputFile = __DIR__ . '/input.mov';   // test uchun input fayl
$outputFile = __DIR__ . '/optimized4.mp4'; // optimized output file
$maxHeight = 1024; // max height

$processor = new VideoProcessor($inputFile);

echo "optimize video \n";

$output = $processor->optimize($outputFile, $maxHeight)
    ->setPreset('slow')
    ->setAudioBitrate(96)
    ->generate(function ($progress) {
        echo "\rOptimize progress: {$progress}%";
    });

echo "\nOptimized file: $output\n";
