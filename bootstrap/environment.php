<?php

declare(strict_types=1);

use App\Support\Config;
use App\Support\Env;

Env::load(base_path('.env'));
Config::loadFromDirectory(config_path());

date_default_timezone_set((string) config('app.timezone', 'UTC'));

