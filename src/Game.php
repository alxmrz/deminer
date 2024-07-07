<?php

namespace Deminer;

use Closure;
use PsyXEngine\Audio;
use PsyXEngine\Event;
use PsyXEngine\GameInterface;
use PsyXEngine\GameObjects;
use PsyXEngine\KeyPressedEvent;
use PsyXEngine\ui\Button;
use PsyXEngine\ui\Element;
use PsyXEngine\ui\Message;
use PsyXEngine\ui\MessageBox;
use SDL2\SDLColor;
use SDL2\SDLRect;

class Game implements GameInterface
{
    private const int MOD_8_X_8 = 0;
    private const int MOD_16_X_8 = 1;
    private const int MOD_30_X_8 = 2;
    private const string VICTORY_SOUND_PATH = __DIR__ . '/../resources/victory_sound.mp3';

    /**
     * [fieldsInXRow, fieldsInYRow, fieldWidthAndHeight]
     *
     * @var array[]
     */
    private array $modes = [
        [8, 8, 75],
        [16, 16, 37],
        [30, 16, 25],
    ];

    private ?int $mode = null;
    private bool $isGameStarted = false;
    /**
     * @var true
     */
    private bool $isGameOver = false;
    private bool $isGameWon = false;
    private bool $isFirstFieldOpened = false;
    private bool $isFlaggedMenuDisplayed = false;
    private ?Audio $audio = null;
    private GameObjects $gameObjects;

    public function init(GameObjects $gameObjects): void
    {
        $this->isGameOver = false;
        $this->gameObjects = $gameObjects;
        $this->isGameStarted = false;
        $this->isGameWon = false;
        $this->isFirstFieldOpened = false;
        $this->mode = null;
        $this->audio = new Audio();
        $this->isFlaggedMenuDisplayed = false;

        $this->addMenu();
    }

    public function update(Event $event = null): void
    {
        if ($event instanceof KeyPressedEvent && $event->isSpacePressed()) {
            $this->restart();
            return;
        }

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

        $fieldsCount = 0;
        $minesCount = 0;
        $openedFields = 0;

        foreach ($this->gameObjects as $gameObject) {
            if ($gameObject instanceof Field) {
                $fieldsCount++;
                $minesCount += (int)$gameObject->isMine;
                $openedFields += (int)($gameObject->isOpen && !$gameObject->isMine);
            }
        }

        $this->isGameWon = $this->isGameStarted && $openedFields === ($fieldsCount - $minesCount);
        if ($this->isGameWon) {
            $this->audio->playChunk(self::VICTORY_SOUND_PATH);
        }
    }
    public function getFields(): GameObjects
    {
        return $this->findObjectsByFilter(function ($gameObject) {
            return $gameObject instanceof Field;
        });
    }

    public function findObjectsByFilter(Closure $filter): GameObjects
    {
        return $this->gameObjects->filter($filter);
    }

    public function setGameOver(): void
    {
        $this->isGameOver = true;
    }

    public function restart(): void
    {
        $this->gameObjects->exchangeArray([]);

        $this->init($this->gameObjects);
    }

    private function addMenu(): void
    {
        $this->gameObjects->add(new MessageBox(
            new SDLRect(300, 100, 300, 500),
            new SDLColor(255, 255, 255, 0)
        ));

        $this->gameObjects->add(new Message(
            "Choose size of game field",
            new SDLRect(310, 120, 290, 90),
            new SDLColor(0, 0, 0, 0)
        ));

        $this->gameObjects->add(new MessageBox(
            new SDLRect(395, 230, 110, 90),
            new SDLColor(0, 255, 0, 0)
        ));
        $this->gameObjects->add(new Button(
            "8 X 8",
            new SDLRect(400, 225, 100, 100),
            new SDLColor(0, 0, 0, 0),
            256,
            function () {
                $this->mode = self::MOD_8_X_8;
            }
        ));

        $this->gameObjects->add(new MessageBox(
            new SDLRect(395, 330, 110, 90),
            new SDLColor(255, 255, 0, 0)
        ));
        $this->gameObjects->add(new Button(
            "16 X 16",
            new SDLRect(400, 330, 100, 100),
            new SDLColor(0, 0, 0, 0),
            256,
            function () {
                $this->mode = self::MOD_16_X_8;
            }
        ));

        $this->gameObjects->add(new MessageBox(
            new SDLRect(395, 430, 110, 90),
            new SDLColor(255, 0, 0, 0)
        ));
        $this->gameObjects->add(new Button(
            "30 X 16",
            new SDLRect(400, 430, 100, 100),
            new SDLColor(0, 0, 0, 0),
            256,
            function () {
                $this->mode = self::MOD_30_X_8;
            }
        ));
    }

    /**
     * @return void
     */
    private function startGame(): void
    {
        $objects = [];

        foreach ($this->gameObjects as $key => $gameObject) {
            if ($gameObject instanceof Element) {
                continue;
            }

            $objects[$key] = $gameObject;
        }
        $this->gameObjects->exchangeArray($objects);

        $color = new SDLColor(30, 30, 30, 0);
        $fWidth = $this->modes[$this->mode][2];
        $xCount = $this->modes[$this->mode][0];
        $yCount = $this->modes[$this->mode][1];

        for ($i = 0; $i < $xCount; $i++) {
            for ($j = 0; $j < $yCount; $j++) {
                $field = new Field(new SDLRect($fWidth * $i, $fWidth * $j, $fWidth, $fWidth), $color);
                $field->game = $this;

                $this->gameObjects->add($field);
            }
        }

        $this->isGameStarted = true;
    }

    private function showGameOverMessage(): void
    {
        if ($this->isFlaggedMenuDisplayed) {
            return;
        }

        $this->gameObjects->add(new MessageBox(
            new SDLRect(95, 130, 310, 180),
            new SDLColor(0, 0, 0, 0)
        ));

        $this->gameObjects->add(new Message(
            "GAME OVER!",
            new SDLRect(95, 130, 310, 90),
            new SDLColor(255, 0, 0, 0)
        ));

        $this->gameObjects->add(new Message(
            "Press SPACE to restart.",
            new SDLRect(95, 200, 310, 90),
            new SDLColor(255, 0, 0, 0)
        ));

        $this->isFlaggedMenuDisplayed = true;
    }

    private function isModeSelected(): bool
    {
        return $this->mode !== null;
    }

    private function showGameWinMessage(): void
    {
        if ($this->isFlaggedMenuDisplayed) {
            return;
        }

        $this->gameObjects->add(new MessageBox(
            new SDLRect(195, 130, 510, 180),
            new SDLColor(0, 255, 0, 0)
        ));

        $this->gameObjects->add(new Message(
            "Congratulations! You are WINNER!",
            new SDLRect(195, 130, 510, 90),
            new SDLColor(0, 0, 0, 0)
        ));

        $this->gameObjects->add(new Message(
            "Press SPACE to play again.",
            new SDLRect(195, 210, 510, 90),
            new SDLColor(0, 0, 0, 0)
        ));

        $this->isFlaggedMenuDisplayed = true;
    }

    public function playAudio(string $audioPath): void
    {
        $this->audio->playChunk($audioPath);
    }

    public function isFirstFieldOpened(): bool
    {
        return $this->isFirstFieldOpened;
    }

    public function setFirstFieldIsOpen(): void
    {
        $this->isFirstFieldOpened = true;
    }
    public function getXFieldsCount(): int
    {
        return $this->modes[$this->mode][0];
    }

    public function getYFieldsCount(): int
    {
        return $this->modes[$this->mode][1];
    }

    public function initMines(Field $firstOpenedField): void
    {
        $xCount = $this->getXFieldsCount();
        $yCount = $this->getYFieldsCount();
        $minesAvailable = floor(15 * ($xCount * $yCount) / 100);
        while ($minesAvailable > 0) {
            $fields = $this->findObjectsByFilter(function ($gameObject) use ($firstOpenedField) {
                return $gameObject instanceof Field && $gameObject->isMine === false && $gameObject !== $firstOpenedField;
            });

            $fields[rand(0, $fields->count() - 1)]->isMine = true;

            $minesAvailable--;
        }

        $this->setFirstFieldIsOpen();
    }

    public function isGameOver(): bool
    {
        return $this->isGameOver;
    }
}