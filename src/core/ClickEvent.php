<?php

namespace Deminer\core;


class ClickEvent extends Event
{
    public bool $isLeftClick = false;
    public bool $isRightClick = false;
    public array $coords = [];

    public function __construct(array $coords, bool $isLeftClick = false, bool $isRightClick = false)
    {
        $this->coords = $coords;
        $this->isLeftClick = $isLeftClick;
        $this->isRightClick = $isRightClick;
    }

}