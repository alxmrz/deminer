<?php

namespace Deminer;

class Collision
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

    public function isCollidedWith(Collision $collision): bool
    {
        return $this->isCollidedByPoint($collision->x, $collision->y)
            || $this->isCollidedByPoint($collision->x + $collision->width, $collision->y)
            || $this->isCollidedByPoint($collision->x, $collision->y + $collision->height)
            || $this->isCollidedByPoint($collision->x+$collision->width, $collision->y + $collision->height);
    }

    private function isCollidedByPoint(int $x, int $y): bool
    {
        return $x >= $this->x  && $x <= ($this->x + $this->width)
            && $y >= $this->y && $y <= ($this->y + $this->height);
    }
}