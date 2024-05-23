<?php

namespace Sapper;

use Sapper\RenderType;
use SDL2\SDLColor;

class Text extends RenderType
{
    private string $text;
    private SDLColor $color;
    private int $size;

    public function __construct(int $x, int $y, int $width, int $height, SDLColor $color,  string $text, int $size = 24)
    {
        parent::__construct($x, $y, $width, $height);

        $this->color = $color;
        $this->text = $text;
        $this->size = $size;
    }
    public function display(Renderer $renderer): void
    {
        $renderer->displayText($this->x, $this->y, $this->width, $this->height, $this->color, $this->text, $this->size);
    }

    public function setColor(SDLColor $color): void
    {
        $this->color = $color;
    }
}