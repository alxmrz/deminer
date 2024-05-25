<?php

namespace Deminer;

use Deminer\core\ClickEvent;
use Deminer\core\Collision;
use Deminer\core\GameObject;
use Deminer\core\Image;
use Deminer\core\Rectangle;
use Deminer\core\Text;
use SDL2\SDLColor;
use SDL2\SDLRect;

class Field extends GameObject
{
    private const string MINE_SOUND = __DIR__ . '/../resources/mine_activation_sound.wav';
    private const string IS_FLAG_IMAGE_PATH = __DIR__ . '/../resources/isFlag.png';
    private const string FLAG_IMAGE_PATH = __DIR__ . '/../resources/flag.png';
    /**
     * @var true
     */
    public bool $isMine = false;
    public bool $isOpen = false;
    public bool $markedAsFlag = false;
    public bool $marksAsUnsure = false;
    public Game $game;
    public int $x;
    public int $y;

    public array $textColors = [
        [0, 0, 0], // 0
        [0, 0, 255], // 1
        [0, 255, 0], // 2
        [255, 0, 0], // 3
        [0, 33, 55], // 4
        [150, 75, 0], // 5
        [48, 213, 200], // 6
        [0, 0, 0], // 7
        [255, 255, 255], // 8
    ];
    private int $width;

    public function __construct(SDLRect $rect, SDLColor $color)
    {
        $this->renderType = new Rectangle(
            $rect->getX() + 1,
            $rect->getY() + 1,
            $rect->getWidth() - 1,
            $rect->getHeight() - 1,
            $color
        );
        $this->collision = new Collision(
            $rect->getX() + 1,
            $rect->getY() + 1,
            $rect->getWidth() - 1,
            $rect->getHeight() - 1
        );

        $this->x = $rect->getX();
        $this->y = $rect->getY();
        $this->width = $rect->getWidth();
    }

    public function onClick(ClickEvent $event): void
    {
        if (!$this->game->isFirstFieldOpened()) {
            $this->game->initMines($this);
        }

        if ($this->isOpen) {
            return;
        }

        if ($event->isLeftClick && !$this->isMarked()) {
            $this->handleLeftClick($event);

            return;
        }

        if ($event->isRightClick) {
            $this->handleRightClick();
        }
    }

    public function isMarked(): bool
    {
        return $this->markedAsFlag || $this->marksAsUnsure;
    }

    private function hasNeighbour(Field $gameObject): bool
    {
        $fWidth = $this->width;
        $isLeftN = $gameObject->x === ($this->x - $fWidth) && $gameObject->y === $this->y;
        $isTopLeftN = $gameObject->x === ($this->x - $fWidth) && $gameObject->y === ($this->y - $fWidth);
        $isTopN = $gameObject->x === $this->x && $gameObject->y === ($this->y - $fWidth);
        $isTopRightN = $gameObject->x === ($this->x + $fWidth) && $gameObject->y === ($this->y - $fWidth);
        $isRightN = $gameObject->x === ($this->x + $fWidth) && $gameObject->y === $this->y;
        $isRightBottomN = $gameObject->x === ($this->x + $fWidth) && $gameObject->y === ($this->y + $fWidth);
        $isBottomN = $gameObject->x === $this->x && $gameObject->y === ($this->y + $fWidth);
        $isLeftBottomN = $gameObject->x === ($this->x - $fWidth) && $gameObject->y === ($this->y + $fWidth);

        return $isLeftN
            || $isTopLeftN
            || $isTopN
            || $isTopRightN
            || $isRightN
            || $isRightBottomN
            || $isBottomN
            || $isLeftBottomN;
    }

    /**
     * @param ClickEvent $event
     * @return void
     */
    public function handleLeftClick(ClickEvent $event): void
    {
        $this->isOpen = true;
        if ($this->isMine) {
            $this->renderType = new Image(
                __DIR__ . '/../resources/mine.png',
                new SDLRect(
                    $this->renderType->x,
                    $this->renderType->y,
                    $this->renderType->width,
                    $this->renderType->height
                )
            );
            $this->game->setGameOver();

            $this->game->playAudio(self::MINE_SOUND);
        } else {
            $minesCount = 0;
            $fieldsFound = [];

            foreach ($this->game->getFields() as $gameObject) {
                if ($gameObject instanceof Field) {
                    if (count($fieldsFound) === 8) {
                        break;
                    }

                    if ($this->hasNeighbour($gameObject)) {
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

            if ($minesCount === 0) {
                $this->renderType = new Rectangle(
                    $this->renderType->x,
                    $this->renderType->y,
                    $this->renderType->width,
                    $this->renderType->height, new SDLColor(255, 255, 255, 0)
                );
            } else {
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
        }
    }

    /**
     * @return void
     */
    public function handleRightClick(): void
    {
        if (!$this->isMarked()) {
            $this->renderType = new Image(
                self::FLAG_IMAGE_PATH,
                new SDLRect(
                    $this->renderType->x,
                    $this->renderType->y,
                    $this->renderType->width,
                    $this->renderType->height
                )
            );
            $this->markedAsFlag = true;
        } elseif ($this->markedAsFlag) {
            $this->renderType = new Image(
                self::IS_FLAG_IMAGE_PATH,
                new SDLRect(
                    $this->renderType->x,
                    $this->renderType->y,
                    $this->renderType->width,
                    $this->renderType->height
                )
            );
            $this->markedAsFlag = false;
            $this->marksAsUnsure = true;
        } else {
            $this->renderType = new Rectangle(
                $this->renderType->x,
                $this->renderType->y,
                $this->renderType->width,
                $this->renderType->height, new SDLColor(30, 30, 30, 0)
            );
            $this->markedAsFlag = false;
            $this->marksAsUnsure = false;
        }
    }
}