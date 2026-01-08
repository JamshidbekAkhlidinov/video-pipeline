# Video Pipeline

A production-ready PHP library for video processing using FFmpeg.

## Features

- Video optimization with resizing to specified max height while preserving aspect ratio
- MP4 optimization with H.264 and AAC encoding
- HLS generation with multiple renditions based on height
- Automatic bitrate calculation for video and audio
- FFprobe integration for video metadata extraction
- Progress tracking with callbacks
- Hardware acceleration support (NVENC, fallback to software)

## Installation

```bash
composer require ustadev/video-pipeline
```

## Usage

### Video Optimization

```php
use ustadev\videopipeline\video\VideoProcessor;

$processor = new VideoProcessor('input.mp4');

// Optimize video with max height 720, CRF 20, preset slow, audio bitrate 192k
$optimize = $processor->optimize('output_optimized.mp4', 720)
    ->setCrf(20)
    ->setPreset('slow')
    ->setAudioBitrate(192);

$outputFile = $optimize->generate(function($progress) {
    echo "Progress: {$progress}%\n";
});
```

### HLS Generation

```php
$processor = new VideoProcessor('input.mp4');

// Generate HLS with custom heights (360p, 720p)
$hls = $processor->generateHls('/path/to/output/dir', [360, 720])
             ->setMasterFileName("ustadev")
             ->setDuration(30);;

$masterPlaylist = $hls->generate(function($progress) {
    echo "Progress: {$progress}%\n";
});
```

### Hardware Acceleration

```php
// Optimize with hardware acceleration
$optimize = $processor->optimize('output_hw.mp4', 1080)->enableHardwareAccel();
$outputFile = $optimize->generate();

// HLS with hardware acceleration
$hls = $processor->generateHls('/path/to/output/dir')->enableHardwareAccel();
$masterPlaylist = $hls->generate();
```

## Requirements

- PHP 7.4+
- FFmpeg with libx264
- FFprobe

## License

MIT# video-pipeline
