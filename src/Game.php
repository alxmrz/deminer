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

    private ?ClickEvent $clickEvent = null;

    public function init(): int
    {
        $this->sdl = LibSDL2::load();
        $this->window = new Window("Miner", 50, 50, 500, 500);

        $this->window->display();

        $this->renderer = $this->window->createRenderer();
        $this->state = new GameState();

        $this->state->init();

        $this->clickEvent = null;

        return 1;
    }

    public function run(): void
    {
        $this->init();

        while ($this->isRunning) {
            $this->handleEvents();
            $this->state->update($this->clickEvent);

            $this->renderer->render($this->state->gameObjects);

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
                    $this->state->restart();
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