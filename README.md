# Video Pipeline

A production-ready PHP library for video processing using FFmpeg.

## Features

- Smart video resize with aspect ratio preservation
- MP4 optimization with H.264 and AAC
- HLS generation with multiple renditions
- Automatic bitrate calculation
- FFprobe integration for video metadata
- Thumbnail generation
- Progress tracking with callbacks
- Hardware acceleration support (NVENC, fallback to software)

## Installation

```bash
composer require ustadev/video-pipeline
```

## Usage

### Basic Video Processing

```php
use ustadev\videopipeline\video\VideoProcessor;

$processor = new VideoProcessor('input.mp4');

// Resize video
$resize = $processor->resize(720)->setCrf(20);
$processor->process($resize, 'output_resized.mp4');

// Optimize MP4
$optimize = $processor->optimize()->setAudioBitrate(192);
$processor->process($optimize, 'output_optimized.mp4');

// Generate thumbnail
$thumbnail = $processor->thumbnail()->setTimestamp(5.0)->setSize(640, 360);
$processor->process($thumbnail, 'thumbnail.jpg');

// Generate HLS
$hls = $processor->generateHls('/path/to/output/dir');
$masterPlaylist = $hls->generate(function($progress) {
    echo "Progress: {$progress}%\n";
});
```

### Hardware Acceleration

```php
$optimize = $processor->optimize()->enableHardwareAccel();
$processor->process($optimize, 'output_hw.mp4');

$hls = $processor->generateHls('/path/to/output/dir')->enableHardwareAccel();
$masterPlaylist = $hls->generate();
```

## Requirements

- PHP 7.4+
- FFmpeg with libx264
- FFprobe

## License

MIT# video-pipeline
