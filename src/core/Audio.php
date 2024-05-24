<?php

namespace Deminer\core;

use SDL2\LibSDL2;
use SDL2\LibSDL2Mixer;

class Audio
{
    private LibSDL2Mixer $mixer;

    public function __construct()
    {
        $this->mixer = LibSDL2Mixer::load();

    }

    public function play(string $audioPath): void
    {
        if ($this->mixer->Mix_OpenAudio(44100, LibSDL2Mixer::DEFAULT_FORMAT, 2, 2048) === 0) {
            $chunk = $this->mixer->Mix_LoadWAV($audioPath, LibSDL2::load());
            $this->mixer->Mix_PlayChannel(-1, $chunk, 0);
        } else {
            printf("ERROR ON open audio: %s\n", LibSDL2::load()->SDL_GetError());
        }
    }
}