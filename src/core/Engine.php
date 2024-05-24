<?php

namespace Deminer\core;

use Deminer\Game;
use SDL2\KeyCodes;
use SDL2\LibSDL2;
use SDL2\SDLEvent;

class Engine
{
    private const int WINDOW_START_X = 200;
    private const int WINDOW_START_Y = 200;
    public const int WINDOW_WIDTH = 900;
    public const int WINDOW_HEIGHT = 600;

    private LibSDL2 $sdl;
    private Window $window;

    private bool $isRunning = true;
    private Renderer $renderer;
    private GameInterface $game;

    private ?ClickEvent $clickEvent = null;

    private function init(): void
    {
        $this->sdl = LibSDL2::load();
        $this->window = new Window(
            "Miner",
            self::WINDOW_START_X,
            self::WINDOW_START_Y,
            self::WINDOW_WIDTH,
            self::WINDOW_HEIGHT
        );

        $this->window->display();

        $this->renderer = $this->window->createRenderer();
        $this->game = new Game();

        $this->game->init();

        $this->clickEvent = null;
    }

    public function run(): void
    {
        $this->init();

        while ($this->isRunning) {
            $this->handleEvents();
            $this->game->update($this->clickEvent);

            $this->game->draw($this->renderer);

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
                $this->isRunning = false;
                continue;
            }

            if (SDLEvent::SDL_MOUSEBUTTONDOWN === $windowEvent->type) {
                if ($windowEvent->button->button === KeyCodes::SDL_BUTTON_LEFT) {
                    $eventClick = new ClickEvent([$windowEvent->button->x, $windowEvent->button->y], true, false);
                    $this->setClickEvent($eventClick);
                } elseif ($windowEvent->button->button === KeyCodes::SDL_BUTTON_RIGHT) {
                    $eventClick = new ClickEvent([$windowEvent->button->x, $windowEvent->button->y], false, true);
                    $this->setClickEvent($eventClick);
                }
            }

            if (SDLEvent::SDL_KEYDOWN == $windowEvent->type) {
                if ($windowEvent->key->keysym->sym == KeyCodes::SDLK_SPACE) {
                    $this->game->restart();
                    printf("Pressed space\n");
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