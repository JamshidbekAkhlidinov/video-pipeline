<?php

namespace ustadev\videopipeline\video;

use ustadev\videopipeline\contracts\ProcessorInterface;
use ustadev\videopipeline\ffmpeg\CommandBuilder;

class Thumbnail implements ProcessorInterface
{
    private float $timestamp = 1.0;
    private int $width = 320;
    private int $height = 180;
    private string $format = 'jpg';

    public function setTimestamp(float $timestamp): self
    {
        $this->timestamp = $timestamp;
        return $this;
    }

    public function setSize(int $width, int $height): self
    {
        $this->width = $width;
        $this->height = $height;
        return $this;
    }

    public function setFormat(string $format): self
    {
        $this->format = $format;
        return $this;
    }

    public function apply(CommandBuilder $builder): void
    {
        $builder->addOption('-ss', (string) $this->timestamp)
                ->addOption('-vframes', '1')
                ->addOption('-vf', "scale={$this->width}:{$this->height}");
    }
}