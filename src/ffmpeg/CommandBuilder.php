<?php

namespace ustadev\videopipeline\ffmpeg;

class CommandBuilder
{
    private array $parts = [];

    public function __construct()
    {
        $this->parts[] = 'ffmpeg';
    }

    public function input(string $file): self
    {
        $this->parts[] = '-i';
        $this->parts[] = escapeshellarg($file);
        return $this;
    }

    public function output(string $file): self
    {
        $this->parts[] = escapeshellarg($file);
        return $this;
    }

    public function addOption(string $option, string $value = null): self
    {
        $this->parts[] = $option;
        if ($value !== null) {
            // For -vf and -filter_complex, add quotes but don't escape to preserve complex expressions
            if ($option === '-vf' || $option === '-filter_complex') {
                $this->parts[] = '"' . $value . '"';
            } else {
                $this->parts[] = escapeshellarg($value);
            }
        }
        return $this;
    }

    public function addRaw(string $raw): self
    {
        $this->parts[] = $raw;
        return $this;
    }

    public function build(): string
    {
        return implode(' ', $this->parts);
    }
}