<?php
require __DIR__ . '/vendor/autoload.php';

use ustadev\videopipeline\video\VideoProcessor;


$inputFile = __DIR__ . '/input.mov';   // test uchun input fayl
$outputFile = __DIR__ . '/optimized1.mp4'; // optimized output file
$maxHeight = 720; // max height

$processor = new VideoProcessor($inputFile);

echo "optimize video \n";

$output = $processor->optimize($outputFile, $maxHeight)
    ->setPreset('slow')
    ->setAudioBitrate(96)
    ->setCrf(26)
    ->generate();

echo "\nOptimized file: $output\n";
