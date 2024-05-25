<?php

use Deminer\core\Engine;
use Deminer\Game;

require_once __DIR__ . '/../vendor/autoload.php';

(new Engine())->run(new Game());
