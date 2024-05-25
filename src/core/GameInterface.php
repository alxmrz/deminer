<?php

namespace Deminer\core;

interface GameInterface
{
    public function init(): void;

    public function update(?Event $event = null): void;
    public function draw(Renderer $renderer): void;
}