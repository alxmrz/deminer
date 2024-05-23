<?php

namespace Sapper;

use SDL2\LibSDL2;
use SDL2\LibSDL2TTF;
use SDL2\SDLColor;
use SDL2\SDLRect;
use SDL2\SDLRenderer;

class Renderer
{
    private LibSDL2 $sdl;
    private Window $window;
    private SDLRenderer $renderer;
    private LibSDL2TTF $ttf;
    private array $fonts = [];

    public function __construct(Window $window)
    {
        $this->sdl = LibSDL2::load();
        $this->ttf = LibSDL2TTF::load();
        $this->window = $window;
    }

    public function __destruct()
    {
        foreach ($this->fonts as $font) {
            $this->ttf->TTF_CloseFont($font);
        }
        $this->ttf->TTF_Quit();
    }

    public function init(): int
    {
        $renderer = $this->sdl->SDL_CreateRenderer($this->window->getWindow(), -1, 2);
        if ($renderer === null) {
            echo "ERROR ON INIT: " . $this->sdl->SDL_GetError();

            return 0;
        }

        $this->renderer = $renderer;
        $this->ttf->TTF_Init();

        return 1;
    }

    public function render(array $gameObjects): void
    {
        if ($this->sdl->SDL_RenderClear($this->renderer) < 0) {
            printf("Cant clear renderer: %s\n", $this->sdl->SDL_GetError());
            $this->window->close();

            exit();
        }

        $this->renderScene();

        $this->renderGameObjects($gameObjects);

        $this->sdl->SDL_RenderPresent($this->renderer);
    }

    /**
     * @param GameObject[] $gameObjects
     * @return void
     */
    public function renderGameObjects(array $gameObjects): void
    {
        foreach ($gameObjects as $gameObject) {
            $gameObject->getRenderType()->display($this);
        }
    }

    public function destroy(): void
    {
        $this->sdl->SDL_DestroyRenderer($this->renderer);
    }

    private function renderScene(): void
    {
        $this->sdl->SDL_SetRenderDrawColor($this->renderer, 160, 160, 160, 0);

        $mainRect = new SDLRect(0, 0, 800, 600);

        $mainRect->setX(0);
        $mainRect->setY(0);
        $mainRect->setWidth(800);
        $mainRect->setHeight(600);

        if ($this->sdl->SDL_RenderFillRect($this->renderer, $mainRect) < 0) {
            echo "ERROR ON INIT: " . $this->sdl->SDL_GetError();
            $this->window->close();
        }
    }

    public function fillRect(int $x, int $y, int $width, int $height, SDLColor $color): void
    {
        $this->sdl->SDL_SetRenderDrawColor($this->renderer, $color->r, $color->g, $color->b, $color->a);

        $mainRect = new SDLRect($x, $y, $width, $height);

        if ($this->sdl->SDL_RenderFillRect($this->renderer, $mainRect) < 0) {
            echo "ERROR ON INIT: " . $this->sdl->SDL_GetError();
            $this->window->close();
        }
    }

    public function displayText(int $x, int $y, int $width, int $height, SDLColor $color, string $text, int $size = 24): void
    {
        $sans = $this->getFont(__DIR__ . '/../resources/Sans.ttf', $size);

        $surfaceMessage = $this->ttf->TTF_RenderText_Solid($sans, $text, $color);
        if ($surfaceMessage === null) {
            printf("Can't create title surface: %s\n", $this->sdl->SDL_GetError());
            $this->ttf->TTF_CloseFont($sans);
            $this->ttf->TTF_Quit();
            $this->window->close();

            exit();
        }

        $textureMessage = $this->sdl->SDL_CreateTextureFromSurface($this->renderer, $surfaceMessage);
        if (!$textureMessage) {
            printf("Can't create texture: %s\n", $this->sdl->SDL_GetError());
            $this->sdl->SDL_FreeSurface($surfaceMessage);
            $this->ttf->TTF_CloseFont($sans);
            $this->ttf->TTF_Quit();
            $this->window->close();

            exit();
        }

        $messageRect = new SDLRect($x, $y, $width, $height);

        if ($this->sdl->SDL_RenderCopy($this->renderer, $textureMessage, null, $messageRect) !== 0) {
            printf("Error on copy: %s\n", $this->sdl->SDL_GetError());

            $this->sdl->SDL_FreeSurface($surfaceMessage);
            $this->ttf->TTF_CloseFont($sans);
            $this->ttf->TTF_Quit();
            $this->window->close();

            exit();
        }

        $this->sdl->SDL_DestroyTexture($textureMessage);
        $this->sdl->SDL_FreeSurface($surfaceMessage);
    }

    private function getFont(string $path, int $size): object
    {
        if (!isset($this->fonts[$size])) {
            $sans = $this->ttf->TTF_OpenFont($path,$size);
            if ($sans === null) {
                printf("Can't create font: %s\n", $this->sdl->SDL_GetError());
                $this->ttf->TTF_Quit();
                $this->window->close();

                exit();
            }

            $this->fonts[$size] = $sans;
        }

        return $this->fonts[$size];
    }
}