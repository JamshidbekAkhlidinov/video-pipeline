<?php

namespace ustadev\videopipeline\video\hls;

class Rendition
{
    public int $height;
    public int $videoBitrate;
    public int $audioBitrate;
    public int $maxrate;
    public int $bufsize;

    public function __construct(int $height, int $videoBitrate, int $audioBitrate)
    {
        $this->height = $height;
        $this->videoBitrate = $videoBitrate;
        $this->audioBitrate = $audioBitrate;
        $this->maxrate = (int) ($videoBitrate * 1.2);
        $this->bufsize = $videoBitrate * 2;
    }
}