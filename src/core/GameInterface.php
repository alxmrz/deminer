<?php

namespace Deminer\core;

interface GameInterface
{
    public function update(): void;
    public function draw(Renderer $renderer): void;
}