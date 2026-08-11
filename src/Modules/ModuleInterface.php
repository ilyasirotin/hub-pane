<?php

declare(strict_types=1);

namespace Modules;

interface ModuleInterface
{
    public function getTitle(): string;

    public function render(): string;
}
