<?php

namespace ustadev\videopipeline\support;

class ProgressParser
{
    private float $duration;

    public function __construct(float $duration)
    {
        $this->duration = $duration;
    }

    public function parseProgress(string $line): ?float
    {
        if (preg_match('/time=(\d{2}):(\d{2}):(\d{2}\.\d{2})/', $line, $matches)) {
            $hours = (int) $matches[1];
            $minutes = (int) $matches[2];
            $seconds = (float) $matches[3];

            $currentTime = $hours * 3600 + $minutes * 60 + $seconds;

            if ($this->duration > 0) {
                return ($currentTime / $this->duration) * 100;
            }
        }

        return null;
    }
}