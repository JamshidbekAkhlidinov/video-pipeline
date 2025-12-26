<?php

namespace ustadev\videopipeline\contracts;

use ustadev\videopipeline\ffmpeg\CommandBuilder;

interface ProcessorInterface
{
    public function apply(CommandBuilder $builder): void;
}