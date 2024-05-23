<?php

namespace Sapper;

class GameState
{

    /**
     * @param GameObject[] $gameObjects
     * @param ClickEvent|null $clickEvent
     * @return void
     */
    public function update(array $gameObjects, ?ClickEvent $clickEvent = null): void
    {
        if (!$clickEvent) {
            $mouseCollision = null;
        } else {
            $mouseCollision = new Collision($clickEvent->coords[0], $clickEvent->coords[1], 1, 1);
        }

        foreach($gameObjects as $gameObject) {
            if ($mouseCollision && $gameObject->isCollidable()
                && $gameObject->getCollision()->isCollidedWith($mouseCollision)) {
                $gameObject->onClick($clickEvent);
            }
        }
    }
}