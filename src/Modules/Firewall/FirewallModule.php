<?php

declare(strict_types=1);

namespace Modules\Firewall;

use Modules\ModuleInterface;

final class FirewallModule implements ModuleInterface
{

    public function getTitle(): string
    {
        return 'Firewall';
    }

    public function render(): string
    {
        return file_get_contents(__DIR__ . '/templates/firewall.html');
    }
}
