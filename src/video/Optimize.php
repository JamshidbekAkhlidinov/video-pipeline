<?php

namespace ustadev\videopipeline\video;

use ustadev\videopipeline\ffmpeg\Ffmpeg;

class Optimize
{
    private Ffmpeg $ffmpeg;
    private string $inputFile;
    private string $outputFile;
    private array $info;
    private int $maxHeight;
    private int $crf = 23;
    private string $preset = 'medium';
    private int $audioBitrate = 128;
    private bool $hardwareAccel = false;

    public function __construct(Ffmpeg $ffmpeg, string $inputFile, string $outputFile, array $info, int $maxHeight = 1080)
    {
        $this->ffmpeg = $ffmpeg;
        $this->inputFile = $inputFile;
        $this->outputFile = $outputFile;
        $this->info = $info;
        $this->maxHeight = $maxHeight;
    }

    public function setCrf(int $crf): self
    {
        $this->crf = $crf;
        return $this;
    }

    public function setPreset(string $preset): self
    {
        $this->preset = $preset;
        return $this;
    }

    public function setAudioBitrate(int $bitrate): self
    {
        $this->audioBitrate = $bitrate;
        return $this;
    }

    public function enableHardwareAccel(): self
    {
        $this->hardwareAccel = true;
        return $this;
    }

    public function generate(callable $progressCallback = null): string
    {
        $outputDir = dirname($this->outputFile);
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        $height = min($this->info['height'], $this->maxHeight);
        $codec = $this->hardwareAccel ? 'h264_nvenc' : 'libx264';

        $command = $this->ffmpeg->command()
            ->input($this->inputFile);

        if ($height < $this->info['height']) {
            $command->addOption('-vf', "scale=-2:{$height}");
        }

        $command->addOption('-c:v', $codec)
            ->addOption('-crf', (string) $this->crf)
            ->addOption('-preset', $this->preset)
            ->addOption('-movflags', '+faststart')
            ->addOption('-map', '0:v:0')
            ->addOption('-map', '0:a?')
            ->addOption('-c:a', 'aac')
            ->addOption('-b:a', $this->audioBitrate . 'k')
            ->output($this->outputFile);

        $this->ffmpeg->run($progressCallback, $this->info['duration']);

        return $this->outputFile;
    }
}