<?php

namespace ustadev\videopipeline\video;

use ustadev\videopipeline\contracts\ProcessorInterface;
use ustadev\videopipeline\ffmpeg\CommandBuilder;

class Optimize implements ProcessorInterface
{
    private int $crf = 23;
    private string $preset = 'medium';
    private int $audioBitrate = 128;
    private bool $hardwareAccel = false;

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

    public function apply(CommandBuilder $builder): void
    {
        $codec = $this->hardwareAccel ? 'h264_nvenc' : 'libx264';
        $builder->addOption('-c:v', $codec)
                ->addOption('-crf', (string) $this->crf)
                ->addOption('-preset', $this->preset)
                ->addOption('-movflags', '+faststart')
                ->addOption('-map', '0:v:0')
                ->addOption('-map', '0:a?')
                ->addOption('-c:a', 'aac')
                ->addOption('-b:a', $this->audioBitrate . 'k');
    }
}