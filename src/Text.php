<?php

namespace Sapper;

use Sapper\RenderType;
use SDL2\SDLColor;

class Text extends RenderType
{
    private string $text;
    private SDLColor $color;

    public function __construct(int $x, int $y, int $width, int $height, SDLColor $color,  string $text)
    {
        parent::__construct($x, $y, $width, $height);

        $this->color = $color;
        $this->text = $text;
    }
    public function display(Renderer $renderer): void
    {
        $renderer->displayText($this->x, $this->y, $this->width, $this->height, $this->color, $this->text);
    }
}