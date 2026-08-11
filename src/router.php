<?php

declare(strict_types=1);

namespace HubPane;

use Modules\ModuleInterface;
use Modules\Firewall\FirewallModule;
use Modules\WireGuard\WireGuardConfigsModule;
use Modules\WireGuard\WireGuardStatusModule;

/** @return array<string, class-string<ModuleInterface>> */
function routes(): array
{
    return [
        '/' => WireGuardConfigsModule::class,
        '/wireguard/configs' => WireGuardConfigsModule::class,
        '/wireguard' => WireGuardStatusModule::class,
        '/firewall' => FirewallModule::class,
    ];
}

function resolveModule(string $requestUri): ?ModuleInterface
{
    $path = parse_url($requestUri, PHP_URL_PATH) ?: '/';
    $path = rtrim($path, '/') ?: '/';

    $moduleClass = routes()[$path] ?? null;

    return $moduleClass !== null ? new $moduleClass() : null;
}
