<?php

namespace Deminer\ui;

use Deminer\GameObject;
use Deminer\Text;
use SDL2\SDLColor;
use SDL2\SDLRect;

class Message extends Element
{
    public function __construct(string $message, SDLRect $rect, SDLColor $color)
    {
        $this->renderType = new Text(
            $rect->getX(),
            $rect->getY(),
            $rect->getWidth(),
            $rect->getHeight(),
            $color,
            $message,
            50
        );
    }
}