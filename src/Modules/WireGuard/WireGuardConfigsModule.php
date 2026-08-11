<?php

declare(strict_types=1);

namespace Modules\WireGuard;

use Modules\ModuleInterface;

final class WireGuardConfigsModule implements ModuleInterface
{
    public function getTitle(): string
    {
        return 'WireGuard - Client configs';
    }

    public function render(): string
    {
        return file_get_contents(__DIR__ . '/templates/configs.html');
    }
}
