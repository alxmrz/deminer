<?php

namespace Deminer\core;

use SDL2\LibSDL2;
use SDL2\LibSDL2Image;
use SDL2\LibSDL2Mixer;
use SDL2\LibSDL2TTF;
use SDL2\SDLWindow;

class Window
{
    private LibSDL2 $sdl;
    private SDLWindow $window;
    private Renderer $renderer;

    private bool $sdlInited = false;
    private bool $sdlWindowCreated = false;

    private bool $rendererCreated = false;
    private string $title;
    private int $x;
    private int $y;
    private int $width;
    private int $height;

    public function __construct(string $title, int $x, int $y, int $width, int $height)
    {
        $this->sdl = LibSDL2::load();
        $this->title = $title;
        $this->x = $x;
        $this->y = $y;
        $this->width = $width;
        $this->height = $height;
    }

    public function display()
    {
        if ($this->sdl->SDL_Init(LibSDL2::INIT_EVERYTHING) !== 0) {
            echo "ERROR ON INIT: " . $this->sdl->SDL_GetError();

            return 0;
        }

        $this->sdlInited = true;

        $window = $this->sdl->SDL_CreateWindow(
            $this->title,
            $this->x,
            $this->y,
            $this->width,
            $this->height,
            4
        );

        $this->window = $window;
        $this->sdlWindowCreated = true;
    }

    public function close(): void
    {
        if ($this->rendererCreated) {
            $this->renderer->destroy();
            $this->rendererCreated = false;
        }

        if ($this->sdlWindowCreated) {
            $this->sdl->SDL_DestroyWindow($this->window);
            $this->sdlWindowCreated = false;
        }

        if ($this->sdlInited) {
            $this->sdl->SDL_Quit();
            $this->sdlInited = false;
        }
    }

    public function getWindow(): SDLWindow
    {
        return $this->window;
    }

    public function createRenderer(LibSDL2 $sdl2, LibSDL2TTF $ttf, LibSDL2Image $imager): Renderer
    {
        if ($this->rendererCreated) {
            return $this->renderer;
        }
        $this->renderer = new Renderer($this, $sdl2, $ttf, $imager);
        if (!$this->renderer->init()) {
            $this->close();
        }
        $this->rendererCreated = true;

        return $this->renderer;
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function getHeight(): int
    {
        return $this->height;
    }
}