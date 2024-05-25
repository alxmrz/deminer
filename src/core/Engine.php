<?php

namespace Deminer\core;

use Deminer\Game;
use SDL2\KeyCodes;
use SDL2\LibSDL2;
use SDL2\LibSDL2Image;
use SDL2\LibSDL2Mixer;
use SDL2\LibSDL2TTF;
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

    private ?Event $event = null;
    private LibSDL2TTF $ttf;
    private LibSDL2Image $imager;
    private LibSDL2Mixer $mixer;

    private function init(): void
    {
        $this->sdl = LibSDL2::load();
        $this->ttf = LibSDL2TTF::load();
        $this->imager = LibSDL2Image::load();
        $this->mixer = LibSDL2Mixer::load();

        $this->window = new Window(
            "Deminer",
            self::WINDOW_START_X,
            self::WINDOW_START_Y,
            self::WINDOW_WIDTH,
            self::WINDOW_HEIGHT
        );

        $this->window->display();

        $this->renderer = $this->window->createRenderer($this->sdl, $this->ttf, $this->imager);
        $this->game = new Game($this->createAudio());

        $this->game->init();

        $this->event = null;
    }

    public function run(): void
    {
        $this->init();

        while ($this->isRunning) {
            $this->handleEvents();
            $this->game->update($this->event);

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
                    $this->setEvent(
                        new ClickEvent(
                            [$windowEvent->button->x, $windowEvent->button->y],
                            true,
                            false
                        )
                    );
                } elseif ($windowEvent->button->button === KeyCodes::SDL_BUTTON_RIGHT) {
                    $this->setEvent(
                        new ClickEvent(
                            [$windowEvent->button->x, $windowEvent->button->y],
                            false,
                            true
                        )
                    );
                }
            }

            if (SDLEvent::SDL_KEYDOWN === $windowEvent->type) {
                if ($windowEvent->key->keysym->sym == KeyCodes::SDLK_SPACE) {
                     $this->setEvent(new KeyPressedEvent(KeyCodes::SDLK_SPACE));
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

    private function setEvent(Event $event): void
    {
        $this->event = $event;
    }

    private function reset(): void
    {
        $this->event = null;
    }

    private function createAudio(): Audio
    {
        return new Audio();
    }
}