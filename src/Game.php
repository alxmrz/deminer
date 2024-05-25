<?php

namespace Deminer;

use Closure;
use Deminer\core\Audio;
use Deminer\core\ClickEvent;
use Deminer\core\Collision;
use Deminer\core\Event;
use Deminer\core\GameInterface;
use Deminer\core\GameObject;
use Deminer\core\KeyPressedEvent;
use Deminer\core\Renderer;
use Deminer\ui\Button;
use Deminer\ui\Element;
use Deminer\ui\Message;
use Deminer\ui\MessageBox;
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

    /**
     * @var GameObject[]
     */
    private array $gameObjects = [];
    private ?int $mode = null;
    private bool $isGameStarted = false;
    /**
     * @var true
     */
    private bool $isGameOver = false;
    private bool $isGameWon = false;
    private bool $isFirstFieldOpened = false;
    private Audio $audio;

    public function __construct(Audio $audio)
    {
        $this->audio = $audio;
    }

    public function init(): void
    {
        $this->isGameOver = false;
        $this->gameObjects = [];
        $this->isGameStarted = false;
        $this->isGameWon = false;
        $this->isFirstFieldOpened = false;
        $this->mode = null;

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

        $mouseCollision = $event instanceof ClickEvent
            ? new Collision($event->coords[0], $event->coords[1], 1, 1)
            : null;

        $fieldsCount = 0;
        $minesCount = 0;
        $openedFields = 0;

        foreach ($this->gameObjects as $gameObject) {
            if ($mouseCollision && $gameObject->isCollidable()
                && $gameObject->getCollision()->isCollidedWith($mouseCollision)) {
                $gameObject->onClick($event);
            }

            if ($gameObject instanceof Field) {
                $fieldsCount++;
                $minesCount += (int)$gameObject->isMine;
                $openedFields += (int)($gameObject->isOpen && !$gameObject->isMine);
            }
        }

        $this->isGameWon = $this->isGameStarted && $openedFields === ($fieldsCount - $minesCount);
        if ($this->isGameWon) {
            $this->audio->play(self::VICTORY_SOUND_PATH);
        }
    }

    public function draw(Renderer $renderer): void
    {
        $renderer->render($this->gameObjects);
    }

    public function getFields(): array
    {
        return $this->findObjectsByFilter(function ($gameObject) {
            return $gameObject instanceof Field;
        });
    }

    public function findObjectsByFilter(Closure $filter): array
    {
        return array_values(array_filter($this->gameObjects, $filter));
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
    private function startGame(): void
    {
        $color = new SDLColor(30, 30, 30, 0);
        $fWidth = $this->modes[$this->mode][2];
        $xCount = $this->modes[$this->mode][0];
        $yCount = $this->modes[$this->mode][1];

        for ($i = 0; $i < $xCount; $i++) {
            for ($j = 0; $j < $yCount; $j++) {
                $field = new Field(new SDLRect($fWidth * $i, $fWidth * $j, $fWidth, $fWidth), $color);
                $field->game = $this;

                $this->gameObjects[] = $field;
            }
        }

        $this->gameObjects = $this->findObjectsByFilter(function ($object) {
            return !($object instanceof Element);
        });

        $this->isGameStarted = true;
    }

    private function showGameOverMessage(): void
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

    private function isModeSelected(): bool
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

    public function playAudio(string $audioPath): void
    {
        $this->audio->play($audioPath);
    }

    public function isFirstFieldOpened(): bool
    {
        return $this->isFirstFieldOpened;
    }

    public function setFirstFieldIsOpen(): void
    {
        $this->isFirstFieldOpened = true;
    }

    public function getMode(): ?int
    {
        return $this->mode;
    }

    public function getXFieldsCount(): int
    {
        return $this->modes[$this->mode][0];
    }

    public function getYFieldsCount(): int
    {
        return $this->modes[$this->mode][1];
    }
}