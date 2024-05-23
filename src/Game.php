<?php

namespace Sapper;

use SDL2\KeyCodes;
use SDL2\LibSDL2;
use SDL2\SDLColor;
use SDL2\SDLEvent;

class Game
{
    private LibSDL2 $sdl;
    private Window $window;

    private bool $isRunning = true;
    private Renderer $renderer;
    private GameState $state;
    /**
     * @var GameObject[]
     */
    public array $gameObjects = [];

    private array $modes = [
        [8, 8],
        [16, 16],
        [30, 16],
    ];
    private ?ClickEvent $clickEvent = null;

    public function init(): int
    {
        $this->sdl = LibSDL2::load();
        $this->window = new Window("Miner", 50, 50, 500, 500);

        $this->window->display();

        $this->renderer = $this->window->createRenderer();
        $this->state = new GameState();


        $color = new SDLColor(30, 30, 30, 0);
        $mode = $this->modes[0];

        $xCount = $mode[0];
        $yCount = $mode[1];
        $minesAvailable = floor(15 * ($xCount * $yCount) / 100);

        //$this->gameObjects[] = new Field(50, 50, 50, 50, $color);

        for ($i = 0; $i < $xCount; $i++) {
            for ($j = 0; $j < $yCount; $j++) {
                $field = new Field(25 * $i, 25 * $j, 25, 25, $color);
                $field->game = $this;

                $this->gameObjects[] = $field;
            }
        }

        while ($minesAvailable > 0) {
            $this->gameObjects[rand(0, count($this->gameObjects)-1)]->isMine = true;
            $minesAvailable--;
        }

        $this->clickEvent = null;

        return 1;
    }

    public function run(): void
    {
        $this->init();

        while ($this->isRunning) {
            $this->handleEvents();
            $this->state->update($this->gameObjects, $this->clickEvent);

            $this->renderer->render($this->gameObjects);

            $this->reset();

            $this->delay(100);
        }

        $this->quit();
    }

    private function handleEvents(): void
    {
        $windowEvent = $this->sdl->createWindowEvent();
        while ($this->sdl->SDL_PollEvent($windowEvent)) {
            if (SDLEvent::SDL_QUIT === $windowEvent->type) {
                printf("Pressed quit button\n");
                $this->isRunning = false;
                continue;
            }

            if (SDLEvent::SDL_MOUSEBUTTONDOWN === $windowEvent->type) {
                if ($windowEvent->button->button === KeyCodes::SDL_BUTTON_LEFT) {
                    $eventClick = new ClickEvent([$windowEvent->button->x, $windowEvent->button->y], true, false);
                    $this->setClickEvent($eventClick);

                    printf(
                        "Pressed left mouse button on: %d, %d\n",
                        $windowEvent->button->x,
                        $windowEvent->button->y
                    );
                } elseif ($windowEvent->button->button === KeyCodes::SDL_BUTTON_RIGHT) {
                    $eventClick = new ClickEvent([$windowEvent->button->x, $windowEvent->button->y], false, true);
                    $this->setClickEvent($eventClick);

                    printf(
                        "Pressed right mouse button on: %d, %d\n",
                        $windowEvent->button->x,
                        $windowEvent->button->y
                    );
                }
            }
        }
    }


    private function quit(): void
    {
        $this->window->close();
    }

    /**
     * @param int $ms
     * @return void
     */
    public function delay(int $ms): void
    {
        $this->sdl->SDL_Delay($ms);
    }

    private function setClickEvent(ClickEvent $event)
    {
        $this->clickEvent = $event;
    }

    private function reset()
    {
        $this->clickEvent = null;
    }
}