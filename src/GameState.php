<?php

namespace Deminer;

use Deminer\ui\Button;
use Deminer\ui\Element;
use Deminer\ui\Message;
use Deminer\ui\MessageBox;
use SDL2\SDLColor;
use SDL2\SDLRect;

class GameState
{
    public const int MOD_8_X_8 = 0;
    public const int MOD_16_X_8 = 1;
    public const int MOD_30_X_8 = 2;

    /**
     * [fieldsInXRow, fieldsInYRow, fieldWidthAndHeight]
     *
     * @var array[]
     */
    public array $modes = [
        [8, 8, 75],
        [16, 16, 37],
        [30, 16, 25],
    ];

    /**
     * @var GameObject[]
     */
    public array $gameObjects = [];
    public ?int $mode = null;
    private bool $isGameStarted = false;
    /**
     * @var true
     */
    private bool $isGameOver = false;
    private bool $isGameWon = false;

    public function init(): void
    {
        $this->isGameOver = false;
        $this->gameObjects = [];
        $this->isGameStarted = false;
        $this->isGameWon = false;
        $this->mode = null;

        $this->addMenu();
    }

    /**
     * @param ClickEvent|null $clickEvent
     * @return void
     */
    public function update(?ClickEvent $clickEvent = null): void
    {
        if ($this->isGameWon) {
            $this->showGameWinMessage();

            return;
        }

        if ($this->isGameOver) {
            $this->showGameOverMessage();

            return;
        }

        if ($this->isModeSelected() && !$this->isGameStarted) {
            $this->startGame();
        }

        $mouseCollision = !is_null($clickEvent)
            ? new Collision($clickEvent->coords[0], $clickEvent->coords[1], 1, 1)
            : null;

        $fieldsCount = 0;
        $minesCount = 0;
        $openedFields = 0;

        foreach ($this->gameObjects as $gameObject) {
            if ($mouseCollision && $gameObject->isCollidable()
                && $gameObject->getCollision()->isCollidedWith($mouseCollision)) {
                $gameObject->onClick($clickEvent);
            }

            if ($gameObject instanceof Field) {
                $fieldsCount++;
                $minesCount += (int)$gameObject->isMine;
                $openedFields += (int)($gameObject->isOpen && !$gameObject->isMine);
            }
        }

        $this->isGameWon = $this->isGameStarted && $openedFields === ($fieldsCount - $minesCount);
    }

    public function getFields(): array
    {
        return array_filter($this->gameObjects, function ($gameObject) {
            return $gameObject instanceof Field;
        });
    }

    public function setMode(int $mode): void
    {
        $this->mode = $mode;
    }

    public function setGameOver(): void
    {
        $this->isGameOver = true;
    }

    public function restart(): void
    {
        $this->init();
    }

    private function addMenu(): void
    {
        $this->gameObjects[] = new MessageBox(
            new SDLRect(300, 100, 300, 500),
            new SDLColor(255, 255, 255, 0)
        );

        $this->gameObjects[] = new Message(
            "Choose size of game field",
            new SDLRect(310, 120, 290, 90),
            new SDLColor(0, 0, 0, 0)
        );


        $this->gameObjects[] = new MessageBox(
            new SDLRect(395, 230, 110, 90),
            new SDLColor(0, 255, 0, 0)
        );
        $this->gameObjects[] = new Button(
            "8 X 8",
            new SDLRect(400, 225, 100, 100),
            new SDLColor(0, 0, 0, 0),
            256,
            function () {
                $this->mode = self::MOD_8_X_8;
            }
        );

        $this->gameObjects[] = new MessageBox(
            new SDLRect(395, 330, 110, 90),
            new SDLColor(255, 255, 0, 0)
        );
        $this->gameObjects[] = new Button(
            "16 X 16",
            new SDLRect(400, 330, 100, 100),
            new SDLColor(0, 0, 0, 0),
            256,
            function () {
                $this->mode = self::MOD_16_X_8;
            }
        );

        $this->gameObjects[] = new MessageBox(
            new SDLRect(395, 430, 110, 90),
            new SDLColor(255, 0, 0, 0)
        );
        $this->gameObjects[] = new Button(
            "30 X 16",
            new SDLRect(400, 430, 100, 100),
            new SDLColor(0, 0, 0, 0),
            256,
            function () {
                $this->mode = self::MOD_30_X_8;
            }
        );
    }

    /**
     * @return void
     */
    public function startGame(): void
    {
        $color = new SDLColor(30, 30, 30, 0);
        $fWidth = $this->modes[$this->mode][2];
        $xCount = $this->modes[$this->mode][0];
        $yCount = $this->modes[$this->mode][1];
        $minesAvailable = floor(15 * ($xCount * $yCount) / 100);

        for ($i = 0; $i < $xCount; $i++) {
            for ($j = 0; $j < $yCount; $j++) {
                $field = new Field($fWidth * $i, $fWidth * $j, $fWidth, $fWidth, $color);
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

    /**
     * @return void
     */
    public function showGameOverMessage(): void
    {
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
    }

    /**
     * @return bool
     */
    public function isModeSelected(): bool
    {
        return $this->mode !== null;
    }

    private function showGameWinMessage(): void
    {
        $this->gameObjects[] = new MessageBox(
            new SDLRect(195, 130, 510, 180),
            new SDLColor(0, 255, 0, 0)
        );

        $this->gameObjects[] = new Message(
            "Congratulations! You are WINNER!",
            new SDLRect(195, 130, 510, 90),
            new SDLColor(0, 0, 0, 0)
        );

        $this->gameObjects[] = new Message(
            "Press SPACE to play again.",
            new SDLRect(195, 210, 510, 90),
            new SDLColor(0, 0, 0, 0)
        );
    }
}