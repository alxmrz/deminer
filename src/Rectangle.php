<?php

namespace Sapper;

use SDL2\SDLColor;
use SDL2\SDLRect;

class Rectangle extends RenderType
{
    public int $x;
    public int $y;
    public int $width;
    public int $height;
    public SDLColor $color;

    public function __construct(int $x, int $y, int $width, int $height, SDLColor $color)
    {
        $this->x = $x;
        $this->y = $y;
        $this->width = $width;
        $this->height = $height;
        $this->color = $color;
    }

    public function display(Renderer $renderer): void
    {
        $renderer->displayRect($this->x, $this->y, $this->width, $this->height, $this->color);
    }
}