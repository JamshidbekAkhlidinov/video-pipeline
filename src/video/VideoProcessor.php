<?php

namespace ustadev\videopipeline\video;

use ustadev\videopipeline\ffmpeg\Ffmpeg;
use ustadev\videopipeline\ffmpeg\Probe;
use ustadev\videopipeline\support\ProcessRunner;
use ustadev\videopipeline\video\hls\HlsGenerator;

class VideoProcessor
{
    private Ffmpeg $ffmpeg;
    private Probe $probe;
    private string $inputFile;
    private array $info;

    public function __construct(string $inputFile)
    {
        $runner = new ProcessRunner();
        $this->ffmpeg = new Ffmpeg($runner);
        $this->probe = new Probe($runner);
        $this->inputFile = $inputFile;
        $this->info = $this->probe->getInfo($inputFile);
    }

    public function resize(int $maxHeight): Resize
    {
        return new Resize($maxHeight);
    }

    public function optimize(): Optimize
    {
        return new Optimize();
    }

    public function thumbnail(): Thumbnail
    {
        return new Thumbnail();
    }

    public function generateHls(string $outputDir): HlsGenerator
    {
        return new HlsGenerator($this->ffmpeg, $this->inputFile, $outputDir, $this->info);
    }

    public function process($operation, string $outputFile, callable $progressCallback = null): void
    {
        $builder = $this->ffmpeg->command()->input($this->inputFile)->output($outputFile);
        $operation->apply($builder);
        $this->ffmpeg->run($progressCallback, $this->info['duration']);
    }
}