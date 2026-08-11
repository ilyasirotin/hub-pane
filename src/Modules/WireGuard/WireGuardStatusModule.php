<?php

declare(strict_types=1);

namespace Modules\WireGuard;

use Modules\ModuleInterface;

final class WireGuardStatusModule implements ModuleInterface
{
    public function getTitle(): string
    {
        return 'WireGuard - Peer status';
    }

    public function render(): string
    {
        return file_get_contents(__DIR__ . '/templates/status.html');
    }
}
