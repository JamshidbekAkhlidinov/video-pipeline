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
    private ?array $customHeights = null;
    private string $masterFileName = 'master.m3u8';
    private int $duration = 10;

    public function __construct(Ffmpeg $ffmpeg, string $inputFile, string $outputDir, array $info, ?array $heights = null)
    {
        $this->ffmpeg = $ffmpeg;
        $this->inputFile = $inputFile;
        $this->outputDir = $outputDir;
        $this->info = $info;
        $this->calculator = new BitrateCalculator();
        $this->customHeights = $heights;
        $this->setupRenditions();
    }

    private function setupRenditions(): void
    {
        $heights = $this->customHeights ?? [360, 480, 720, 1080];
        foreach ($heights as $height) {
            if ($this->info['height'] >= $height) {
                $videoBitrate = $this->calculator->calculateVideoBitrate($height);
                $audioBitrate = $this->calculator->calculateAudioBitrate($height);
                $this->renditions[] = new Rendition($height, $videoBitrate, $audioBitrate);
            }
        }
    }

    public function enableHardwareAccel(): self
    {
        $this->hardwareAccel = true;
        return $this;
    }

    public function setMasterFileName($name): self
    {
        if (substr($name, -5) !== '.m3u8') {
            $name .= '.m3u8';
        }
        $this->masterFileName = $name;
        return $this;
    }

    public function setDuration($duration): self
    {
        $this->duration = $duration;
        return $this;
    }

    public function generate(callable $progressCallback = null): string
    {
        $master = new MasterPlaylist();

        foreach ($this->renditions as $rendition) {
            $codec = $this->hardwareAccel ? 'h264_nvenc' : 'libx264';
            $resDir = "{$this->outputDir}/{$rendition->height}";
            if (!is_dir($resDir)) {
                mkdir($resDir, 0755, true);
            }
            $playlistFile = "{$resDir}/index.m3u8";
            $segmentFile = "{$resDir}/segment_%03d.ts";

            $this->ffmpeg->command()
                ->input($this->inputFile)
                ->addOption('-vf', "scale=-2:{$rendition->height}")
                ->addOption('-c:v', $codec)
                ->addOption('-b:v', $rendition->videoBitrate . 'k')
                ->addOption('-maxrate', $rendition->maxrate . 'k')
                ->addOption('-bufsize', $rendition->bufsize . 'k')
                ->addOption('-c:a', 'aac')
                ->addOption('-b:a', $rendition->audioBitrate . 'k')
                ->addOption('-hls_time', $this->duration)
                ->addOption('-hls_playlist_type', 'vod')
                ->addOption('-hls_segment_filename', $segmentFile)
                ->output($playlistFile);

            $this->ffmpeg->run($progressCallback, $this->info['duration']);

            $master->addRendition($rendition, "{$rendition->height}/index.m3u8");
        }

        $masterContent = $master->generate();
        file_put_contents("{$this->outputDir}/{$this->masterFileName}", $masterContent);

        return "{$this->outputDir}/{$this->masterFileName}";
    }
}
