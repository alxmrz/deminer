<?php

namespace Sapper;

use SDL2\SDLColor;

class Field extends GameObject
{
    /**
     * @var true
     */
    public bool $isMine = false;
    public bool $isOpen = false;
    public bool $markedAsFlag = false;
    public bool $marksAsUnsure = false;
    public GameState $gameState;
    public int $x;
    public int $y;

    public array $textColors = [
        [0, 0, 0], // 0
        [0, 0, 255], // 1
        [0, 255, 0], // 2
        [0, 33, 55], // 3
        [63, 161, 119], // 4
        [48, 213, 200], // 5
        [0, 0, 0], // 6
        [255, 255, 255], // 7
    ];

    public function __construct(int $x, int $y, int $width, int $height, SDLColor $color)
    {
        $this->renderType = new Rectangle($x + 1, $y + 1, $width - 1, $height - 1, $color);
        $this->collision = new Collision($x, $y, $width, $height);
        $this->x = $x;
        $this->y = $y;
    }

    public function onClick(ClickEvent $event): void
    {
        if ($this->isOpen) {
            return;
        }

        if ($event->isLeftClick && !$this->isMarked()) {
            $this->isOpen = true;
            if ($this->isMine) {
                $this->renderType->color = new SDLColor(255, 0, 0, 0);
                $this->gameState->setGameOver();
            } else {
                $minesCount = 0;
                $fieldsFound = [];

                foreach ($this->gameState->getFields() as $gameObject) {
                    if ($gameObject instanceof Field) {

                        if (count($fieldsFound) === 8) {
                            break;
                        }

                        if ($this->hasNeighboor($gameObject)) {
                            if ($gameObject->isOpen) {
                                $fieldsFound[] = $gameObject;
                                continue;
                            }

                            if ($gameObject->isMine) {
                                $minesCount++;
                            }

                            $fieldsFound[] = $gameObject;
                        }
                    }
                }

                if ($minesCount === 0) {
                    foreach ($fieldsFound as $field) {
                        $field->onClick($event);
                    }
                }

                $this->renderType = new Text(
                    $this->renderType->x,
                    $this->renderType->y,
                    $this->renderType->width,
                    $this->renderType->height,
                    new SDLColor(
                        $this->textColors[$minesCount][0],
                        $this->textColors[$minesCount][1],
                        $this->textColors[$minesCount][2],
                        0
                    ),
                    "$minesCount"
                );
            }

            return;
        }

        if ($event->isRightClick) {
            if (!$this->isMarked()) {
                $this->renderType->color = new SDLColor(255, 0, 255, 0);
                $this->markedAsFlag = true;
            } elseif ($this->markedAsFlag) {
                $this->renderType->color = new SDLColor(0, 255, 255, 0);
                $this->markedAsFlag = false;
                $this->marksAsUnsure = true;
            } else {
                $this->renderType->color = new SDLColor(30, 30, 30, 0);
                $this->markedAsFlag = false;
                $this->marksAsUnsure = false;
            }
        }
    }

    public function isMarked(): bool
    {
        return $this->markedAsFlag || $this->marksAsUnsure;
    }

    private function hasNeighboor(Field $gameObject): bool
    {
        $isLeftN = $gameObject->x === ($this->x - 25) && $gameObject->y === $this->y;
        $isTopLeftN = $gameObject->x === ($this->x - 25) && $gameObject->y === ($this->y - 25);
        $isTopN = $gameObject->x === $this->x && $gameObject->y === ($this->y - 25);
        $isTopRightN = $gameObject->x === ($this->x + 25) && $gameObject->y === ($this->y - 25);
        $isRightN = $gameObject->x === ($this->x + 25) && $gameObject->y === $this->y;
        $isRightBottomN = $gameObject->x === ($this->x + 25) && $gameObject->y === ($this->y + 25);
        $isBottomN = $gameObject->x === $this->x && $gameObject->y === ($this->y + 25);
        $isLeftBottomN = $gameObject->x === ($this->x - 25) && $gameObject->y === ($this->y + 25);

        return $isLeftN
            || $isTopLeftN
            || $isTopN
            || $isTopRightN
            || $isRightN
            || $isRightBottomN
            || $isBottomN
            || $isLeftBottomN;
    }
}