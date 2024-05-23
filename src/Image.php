<?php

namespace Sapper;

use SDL2\SDLRect;

class Image extends RenderType
{
    private string $imagePath;
    private SDLRect $rect;

    public function __construct(string $imagePath, SDLRect $rect)
    {
        parent::__construct($rect->getX(), $rect->getY(), $rect->getWidth(), $rect->getHeight());
        $this->imagePath = $imagePath;
        $this->rect = $rect;
    }
    public function display(Renderer $renderer): void
    {
        $renderer->displayImage($this->rect, $this->imagePath);
    }
}