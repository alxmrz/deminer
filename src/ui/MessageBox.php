<?php

namespace Deminer\ui;

use SDL2\SDLColor;
use SDL2\SDLRect;

class MessageBox extends Element
{
    public function __construct(SDLRect $rect, SDLColor $color)
    {
        $this->renderType = new Box($rect, $color);
    }
}
