<?php

namespace Sapper;

use SDL2\SDLColor;

class GameState
{
    private array $modes = [
        [8, 8],
        [16, 16],
        [30, 16],
    ];

    /**
     * @var GameObject[]
     */
    public array $gameObjects = [];

    public function init(): void
    {
        $color = new SDLColor(30, 30, 30, 0);
        $mode = $this->modes[0];

        $xCount = $mode[0];
        $yCount = $mode[1];
        $minesAvailable = floor(15 * ($xCount * $yCount) / 100);

        for ($i = 0; $i < $xCount; $i++) {
            for ($j = 0; $j < $yCount; $j++) {
                $field = new Field(25 * $i, 25 * $j, 25, 25, $color);
                $field->gameState = $this;

                $this->gameObjects[] = $field;
            }
        }

        while ($minesAvailable > 0) {
            $this->gameObjects[rand(0, count($this->gameObjects)-1)]->isMine = true;
            $minesAvailable--;
        }
    }

    /**
     * @param ClickEvent|null $clickEvent
     * @return void
     */
    public function update(?ClickEvent $clickEvent = null): void
    {
        if (!$clickEvent) {
            $mouseCollision = null;
        } else {
            $mouseCollision = new Collision($clickEvent->coords[0], $clickEvent->coords[1], 1, 1);
        }

        foreach($this->gameObjects as $gameObject) {
            if ($mouseCollision && $gameObject->isCollidable()
                && $gameObject->getCollision()->isCollidedWith($mouseCollision)) {
                $gameObject->onClick($clickEvent);
            }
        }
    }
}