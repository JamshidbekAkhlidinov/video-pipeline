<?php

namespace ustadev\videopipeline\support;

class BitrateCalculator
{
    private const BITRATE_MAP = [
        360 => ['video' => 800, 'audio' => 128],
        480 => ['video' => 1200, 'audio' => 128],
        720 => ['video' => 2400, 'audio' => 128],
        1080 => ['video' => 4800, 'audio' => 128],
    ];

    public function calculateVideoBitrate(int $height): int
    {
        return self::BITRATE_MAP[$height]['video'] ?? 800;
    }

    public function calculateAudioBitrate(int $height): int
    {
        return self::BITRATE_MAP[$height]['audio'] ?? 128;
    }

    public function calculateMaxrate(int $bitrate): int
    {
        return (int) ($bitrate * 1.2);
    }

    public function calculateBufsize(int $bitrate): int
    {
        return $bitrate * 2;
    }
}