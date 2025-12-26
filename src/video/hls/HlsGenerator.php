<?php

namespace ustadev\videopipeline\video\hls;

use ustadev\videopipeline\ffmpeg\Ffmpeg;
use ustadev\videopipeline\support\BitrateCalculator;

class HlsGenerator
{
    private Ffmpeg $ffmpeg;
    private string $inputFile;
    private string $outputDir;
    private array $info;
    private BitrateCalculator $calculator;
    private array $renditions = [];
    private bool $hardwareAccel = false;

    public function __construct(Ffmpeg $ffmpeg, string $inputFile, string $outputDir, array $info)
    {
        $this->ffmpeg = $ffmpeg;
        $this->inputFile = $inputFile;
        $this->outputDir = $outputDir;
        $this->info = $info;
        $this->calculator = new BitrateCalculator();
        $this->setupRenditions();
    }

    public function enableHardwareAccel(): self
    {
        $this->hardwareAccel = true;
        return $this;
    }

    private function setupRenditions(): void
    {
        $heights = [360, 480, 720, 1080];
        foreach ($heights as $height) {
            if ($this->info['height'] >= $height) {
                $videoBitrate = $this->calculator->calculateVideoBitrate($height);
                $audioBitrate = $this->calculator->calculateAudioBitrate($height);
                $this->renditions[] = new Rendition($height, $videoBitrate, $audioBitrate);
            }
        }
    }

    public function generate(callable $progressCallback = null): string
    {
        $filter = [];
        $map = [];
        $varStreamMap = [];

        foreach ($this->renditions as $i => $rendition) {
            $codec = $this->hardwareAccel ? 'h264_nvenc' : 'libx264';
            $filter[] = "[0:v]scale=if(gt(ih,{$rendition->height}),-2,{$rendition->height})[v{$rendition->height}p]";
            $map[] = "-map [v{$rendition->height}p] -map 0:a:0 " .
                "-c:v:{$i} {$codec} -b:v:{$i} {$rendition->videoBitrate}k -maxrate {$rendition->maxrate}k -bufsize {$rendition->bufsize}k " .
                "-c:a:{$i} aac -b:a:{$i} {$rendition->audioBitrate}k";
            $varStreamMap[] = "v:{$i},a:{$i}";
        }

        $filterComplex = implode('; ', $filter);
        $mapCmd = implode(' ', $map);
        $varStreamMapStr = implode(' ', $varStreamMap);

        $hlsSegmentTemplate = "{$this->outputDir}/%v/segment_%03d.ts";
        $hlsIndexTemplate = "{$this->outputDir}/%v/index.m3u8";
        $masterPlaylist = 'master.m3u8';

        $builder = $this->ffmpeg->command()
            ->input($this->inputFile)
            ->addOption('-filter_complex', $filterComplex)
            ->addRaw($mapCmd)
            ->addOption('-f', 'hls')
            ->addOption('-hls_time', '10')
            ->addOption('-hls_playlist_type', 'vod')
            ->addOption('-hls_segment_filename', $hlsSegmentTemplate)
            ->addOption('-master_pl_name', $masterPlaylist)
            ->addOption('-var_stream_map', $varStreamMapStr)
            ->output($hlsIndexTemplate);

        $this->ffmpeg->run($progressCallback, $this->info['duration']);

        return "{$this->outputDir}/{$masterPlaylist}";
    }
}
