<?php

namespace ustadev\videopipeline\ffmpeg;

use ustadev\videopipeline\support\ProcessRunner;

class Probe
{
    private ProcessRunner $runner;

    public function __construct(ProcessRunner $runner)
    {
        $this->runner = $runner;
    }

    public function getInfo(string $file): array
    {
        $command = "ffprobe -v quiet -print_format json -show_format -show_streams " . escapeshellarg($file);
        $result = $this->runner->run($command);

        if ($result['return_code'] !== 0) {
            throw new \RuntimeException('FFprobe failed: ' . $result['stderr']);
        }

        $data = json_decode($result['stdout'], true);

        if (!$data) {
            throw new \RuntimeException('Invalid FFprobe output');
        }

        $videoStream = null;
        $audioStream = null;

        foreach ($data['streams'] ?? [] as $stream) {
            if ($stream['codec_type'] === 'video') {
                $videoStream = $stream;
            } elseif ($stream['codec_type'] === 'audio') {
                $audioStream = $stream;
            }
        }

        return [
            'width' => $videoStream['width'] ?? null,
            'height' => $videoStream['height'] ?? null,
            'duration' => (float) ($data['format']['duration'] ?? 0),
            'has_audio' => $audioStream !== null,
        ];
    }
}