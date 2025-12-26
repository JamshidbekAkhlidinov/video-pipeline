<?php

namespace ustadev\videopipeline\support;

class ProcessRunner
{
    public function run(string $command, callable $progressCallback = null): array
    {
        $descriptorspec = [
            0 => ['pipe', 'r'], // stdin
            1 => ['pipe', 'w'], // stdout
            2 => ['pipe', 'w'], // stderr
        ];

        $process = proc_open($command, $descriptorspec, $pipes);

        if (!is_resource($process)) {
            throw new \RuntimeException('Failed to start process');
        }

        $stdout = '';
        $stderr = '';

        // Close stdin
        fclose($pipes[0]);

        // Read stdout and stderr
        while (!feof($pipes[1]) || !feof($pipes[2])) {
            $read = [$pipes[1], $pipes[2]];
            $write = null;
            $except = null;

            if (stream_select($read, $write, $except, 0, 100000) > 0) {
                foreach ($read as $stream) {
                    if ($stream === $pipes[1]) {
                        $stdout .= fread($pipes[1], 8192);
                    } elseif ($stream === $pipes[2]) {
                        $line = fgets($pipes[2]);
                        if ($line !== false) {
                            $stderr .= $line;
                            if ($progressCallback) {
                                $progressCallback($line);
                            }
                        }
                    }
                }
            }
        }

        fclose($pipes[1]);
        fclose($pipes[2]);

        $returnCode = proc_close($process);

        return [
            'stdout' => $stdout,
            'stderr' => $stderr,
            'return_code' => $returnCode,
        ];
    }
}