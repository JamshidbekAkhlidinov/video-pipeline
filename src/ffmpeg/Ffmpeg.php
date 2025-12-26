<?php

namespace ustadev\videopipeline\ffmpeg;

use ustadev\videopipeline\support\ProcessRunner;
use ustadev\videopipeline\support\ProgressParser;
use ustadev\videopipeline\ffmpeg\exceptions\FfmpegException;

class Ffmpeg
{
    private ProcessRunner $runner;
    private CommandBuilder $builder;

    public function __construct(ProcessRunner $runner)
    {
        $this->runner = $runner;
        $this->builder = new CommandBuilder();
    }

    public function command(): CommandBuilder
    {
        return $this->builder;
    }

    public function run(callable $progressCallback = null, float $duration = 0): void
    {
        $command = $this->builder->build();

        $parser = $duration > 0 ? new ProgressParser($duration) : null;

        $result = $this->runner->run($command, function ($line) use ($progressCallback, $parser) {
            if ($parser && $progressCallback) {
                $progress = $parser->parseProgress($line);
                if ($progress !== null) {
                    $progressCallback($progress);
                }
            }
        });

        if ($result['return_code'] !== 0) {
            throw new FfmpegException('FFmpeg failed: ' . $result['stderr']);
        }

        // Reset builder for next use
        $this->builder = new CommandBuilder();
    }
}