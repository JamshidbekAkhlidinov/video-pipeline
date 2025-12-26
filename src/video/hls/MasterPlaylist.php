<?php

namespace ustadev\videopipeline\video\hls;

class MasterPlaylist
{
    private array $renditions = [];

    public function addRendition(Rendition $rendition, string $playlistFile): void
    {
        $this->renditions[] = [
            'rendition' => $rendition,
            'playlist' => $playlistFile,
        ];
    }

    public function generate(): string
    {
        $content = "#EXTM3U\n#EXT-X-VERSION:3\n";

        foreach ($this->renditions as $item) {
            $rendition = $item['rendition'];
            $bandwidth = ($rendition->videoBitrate + $rendition->audioBitrate) * 1000;
            $content .= "#EXT-X-STREAM-INF:BANDWIDTH={$bandwidth},RESOLUTION={$rendition->height}p\n";
            $content .= $item['playlist'] . "\n";
        }

        return $content;
    }
}