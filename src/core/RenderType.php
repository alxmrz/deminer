<?php

namespace Deminer\core;

class RenderType
{
    public int $x;
    public int $y;
    public int $width;
    public int $height;

    public function __construct(int $x, int $y, int $width, int $height)
    {
        $this->x = $x;
        $this->y = $y;
        $this->width = $width;
        $this->height = $height;
    }

    public function display(Renderer $renderer): void
    {
        $renderer->fillRect($this->x, $this->y, $this->width, $this->height, $this->color);
    }
}