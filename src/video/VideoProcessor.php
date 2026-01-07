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
        $this->checkFfmpeg();
        $runner = new ProcessRunner();
        $this->ffmpeg = new Ffmpeg($runner);
        $this->probe = new Probe($runner);
        $this->inputFile = $inputFile;
        $this->info = $this->probe->getInfo($inputFile);
    }

    private function checkFfmpeg(): void
    {
        exec('ffmpeg -version 2>&1', $output, $returnVar);
        if ($returnVar !== 0) {
            echo "FFmpeg not found. Install it? (y/n): ";
            $answer = trim(fgets(STDIN));
            if (strtolower($answer) === 'y') {
                $this->installFfmpeg();
            } else {
                echo "FFmpeg is required. Exiting.\n";
                exit(1);
            }
        }
    }

    private function installFfmpeg(): void
    {
        echo "Installing FFmpeg...\n";
        // Detect OS
        if (PHP_OS === 'Linux') {
            // Check for apt (Ubuntu/Debian)
            exec('which apt 2>/dev/null', $output, $returnVar);
            if ($returnVar === 0) {
                exec('sudo apt update && sudo apt install -y ffmpeg', $output, $returnVar);
                if ($returnVar === 0) {
                    echo "FFmpeg installed successfully.\n";
                    return;
                }
            }
            // Check for yum (CentOS/RHEL)
            exec('which yum 2>/dev/null', $output, $returnVar);
            if ($returnVar === 0) {
                exec('sudo yum install -y ffmpeg', $output, $returnVar);
                if ($returnVar === 0) {
                    echo "FFmpeg installed successfully.\n";
                    return;
                }
            }
        }
        echo "Could not install FFmpeg automatically. Please install it manually.\n";
        exit(1);
    }

    public function optimize(): Optimize
    {
        return new Optimize();
    }

    public function generateHls(string $outputDir, array $heights = null): HlsGenerator
    {
        return new HlsGenerator($this->ffmpeg, $this->inputFile, $outputDir, $this->info, $heights);
    }

    public function process($operation, string $outputFile, callable $progressCallback = null): void
    {
        $builder = $this->ffmpeg->command()->input($this->inputFile)->output($outputFile);
        $operation->apply($builder);
        $this->ffmpeg->run($progressCallback, $this->info['duration']);
    }
}