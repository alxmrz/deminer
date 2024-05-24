<?php

namespace Deminer\core;

use SDL2\KeyCodes;

class KeyPressedEvent extends Event
{

    private int $keyCode;

    public function __construct(int $keyCode)
    {
        $this->keyCode = $keyCode;
    }

    public function isSpacePressed(): bool
    {
        return $this->keyCode === KeyCodes::SDLK_SPACE;
    }
}