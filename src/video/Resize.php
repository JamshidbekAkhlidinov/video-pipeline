<?php

namespace ustadev\videopipeline\video;

use ustadev\videopipeline\contracts\ProcessorInterface;
use ustadev\videopipeline\ffmpeg\CommandBuilder;

class Resize implements ProcessorInterface
{
    private int $maxHeight;
    private int $crf = 23;
    private string $preset = 'medium';

    public function __construct(int $maxHeight)
    {
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

    public function apply(CommandBuilder $builder): void
    {
        $scale = "scale=if(gt(ih,{$this->maxHeight}),-2,iw):if(gt(ih,{$this->maxHeight}),{$this->maxHeight},ih)";

        $builder->addOption('-vf', $scale)
                ->addOption('-c:v', 'libx264')
                ->addOption('-crf', (string) $this->crf)
                ->addOption('-preset', $this->preset);
    }
}