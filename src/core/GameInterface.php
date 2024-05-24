<?php

namespace Deminer\core;

interface GameInterface
{
    public function update();
    public function draw();
    public function getWindowSize();
}