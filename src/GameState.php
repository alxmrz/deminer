<?php

namespace Sapper;

use Sapper\ui\Button;
use Sapper\ui\Element;
use Sapper\ui\Message;
use Sapper\ui\MessageBox;
use SDL2\SDLColor;
use SDL2\SDLRect;

class GameState
{
    public const int MOD_8_X_8 = 0;
    public const int MOD_16_X_8 = 1;
    public const int MOD_30_X_8 = 2;

    private array $modes = [
        [8, 8],
        [16, 16],
        [30, 16],
    ];

    /**
     * @var GameObject[]
     */
    public array $gameObjects = [];
    private ?int $mode = null;
    private bool $isGameStarted = false;
    /**
     * @var true
     */
    private bool $isGameOver = false;

    public function init(): void
    {
        $this->isGameOver = false;
        $this->gameObjects = [];
        $this->isGameStarted = false;
        $this->mode = null;
        if ($this->mode === null) {
            $this->addMenu();
        }
    }

    /**
     * @param ClickEvent|null $clickEvent
     * @return void
     */
    public function update(?ClickEvent $clickEvent = null): void
    {
        if ($this->isGameOver) {
            $this->gameObjects[] = new MessageBox(
                new SDLRect(95, 130, 310, 180),
                new SDLColor(0, 0, 0, 0)
            );

            $this->gameObjects[] = new Message(
                "GAME OVER!",
                new SDLRect(95, 130, 310, 90),
                new SDLColor(255, 0, 0, 0)
            );

            $this->gameObjects[] = new Message(
                "Press SPACE to restart.",
                new SDLRect(95, 200, 310, 90),
                new SDLColor(255, 0, 0, 0)
            );

            return;
        }

        if ($this->mode !== null && !$this->isGameStarted) {
            $color = new SDLColor(30, 30, 30, 0);

            $xCount = $this->modes[$this->mode][0];
            $yCount = $this->modes[$this->mode][1];
            $minesAvailable = floor(15 * ($xCount * $yCount) / 100);

            for ($i = 0; $i < $xCount; $i++) {
                for ($j = 0; $j < $yCount; $j++) {
                    $field = new Field(25 * $i, 25 * $j, 25, 25, $color);
                    $field->gameState = $this;

                    $this->gameObjects[] = $field;
                }
            }

            while ($minesAvailable > 0) {
                array_values($this->getFields())[rand(0, count($this->getFields()) - 1)]->isMine = true;

                $minesAvailable--;
            }

            $this->gameObjects = array_filter($this->gameObjects, function ($object) {
                return !($object instanceof Element);
            });

            $this->isGameStarted = true;
        }

        if (!$clickEvent) {
            $mouseCollision = null;
        } else {
            $mouseCollision = new Collision($clickEvent->coords[0], $clickEvent->coords[1], 1, 1);
        }

        foreach ($this->gameObjects as $gameObject) {
            if ($mouseCollision && $gameObject->isCollidable()
                && $gameObject->getCollision()->isCollidedWith($mouseCollision)) {
                $gameObject->onClick($clickEvent);
            }
        }
    }

    public function getFields(): array
    {
        return array_filter($this->gameObjects, function ($gameObject) {
            return $gameObject instanceof Field;
        });
    }

    public function setMode(int $mode): void
    {
        $this->mode =$mode;
    }

    public function setGameOver(): void
    {
        $this->isGameOver = true;
    }

    public function restart()
    {
        $this->init();
    }

    private function addMenu()
    {
        $this->gameObjects[] = new MessageBox(
            new SDLRect(100, 100, 300, 500),
            new SDLColor(255, 255, 255, 0)
        );


        $this->gameObjects[] = new MessageBox(
            new SDLRect(195, 130, 110, 90),
            new SDLColor(0, 255, 255, 0)
        );
        $this->gameObjects[] = new Button(
            "8 X 8",
            new SDLRect(200, 125, 100, 100),
            new SDLColor(0, 0, 0, 0),
            256,
            function () {
                $this->mode = self::MOD_8_X_8;
            }
        );

        $this->gameObjects[] = new MessageBox(
            new SDLRect(195, 230, 110, 90),
            new SDLColor(0, 255, 255, 0)
        );
        $this->gameObjects[] = new Button(
            "16 X 16",
            new SDLRect(200, 230, 100, 100),
            new SDLColor(0, 0, 0, 0),
            256,
            function () {
                $this->mode = self::MOD_16_X_8;
            }
        );

        $this->gameObjects[] = new MessageBox(
            new SDLRect(195, 330, 110, 90),
            new SDLColor(0, 255, 255, 0)
        );
        $this->gameObjects[] = new Button(
            "30 X 16",
            new SDLRect(200, 330, 100, 100),
            new SDLColor(0, 0, 0, 0),
            256,
            function () {
                $this->mode = self::MOD_30_X_8;
            }
        );
    }
}