# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project overview

hub-pane (HubPane) is a WireGuard-based site-to-site network hub modular monitoring system, written in PHP 8.4 with no framework. The app is a front-controller + router + module system: every page is a self-contained "module" that the router dispatches to and that renders its HTML into a shared base layout. Module content currently renders hardcoded stub data (no real backend/monitoring data source is wired up yet).

## Environment & commands

- Runtime: PHP >= 8.4, no framework.
- Local dev runs via Docker Compose (`compose.yaml`): an `nginx:alpine` `web` service proxies PHP requests to an `app` service (`php:8.4-fpm-xdebug3.5`) over FastCGI. Bring the stack up with `docker compose up`. The `app` container runs on a case-sensitive Linux filesystem — namespace segments must match directory casing exactly (dev is typically on case-insensitive macOS/APFS, which silently tolerates mismatches that then break in the container).
- `.env.dist` is the template for a local `.env` (currently empty; copy to `.env` and fill in as configuration is added).
- After adding/renaming a class under `src/`, run `composer dump-autoload` to refresh the autoloader.
- There is no test suite or linter configured — `composer.json` defines only package metadata and the autoload map.

## Autoloading

`composer.json` declares two independent PSR-4 roots:
- `HubPane\` -> `src/` — reserved for app-level code (currently `src/app.php` and `src/router.php`, which are plain procedural scripts loaded via `require`, not autoloaded classes).
- `Modules\` -> `src/Modules/` — deliberately its own top-level namespace, **not** nested under `HubPane\`, so module code stays decoupled from the app root.

## Request flow

`public/index.php` (front controller, the only thing nginx routes to — see `docker/nginx/default.conf`'s `try_files ... /index.php?$query_string`) requires `src/app.php`, which:
1. Loads the Composer autoloader and `src/router.php`.
2. Calls `HubPane\resolveModule($_SERVER['REQUEST_URI'])`, which normalizes the path (strips query string, trims trailing slash) and looks it up in `HubPane\routes()` — a static `path => module class` map. No match returns `null`, which `app.php` turns into a 404 with inline fallback content.
3. Instantiates the matched module (no-arg constructor), pulls `getTitle()`/`render()` from it into `$pageTitle`/`$moduleContent`.
4. Requires `templates/index.phtml`, which has those variables (plus `$requestUri`) in scope since it's a plain `require`, not a function call — no view-model wrapper.

## Module architecture

A module is any class implementing `Modules\ModuleInterface` (`getTitle(): string`, `render(): string`). Each module lives in its own directory under `src/Modules/<Name>/`, and owns its own `templates/` subdirectory alongside it. A module's `render()` typically just `file_get_contents()`s its own template file — see `src/Modules/Firewall/FirewallModule.php` for the simplest example.

A single conceptual "module" (e.g. WireGuard) can expose more than one page: `src/Modules/WireGuard/` has both `WireGuardStatusModule` and `WireGuardConfigsModule`, each with its own class, its own template, and its own route entry in `src/router.php`. There's no shared base class beyond the interface — adding a page means adding a class + a template + one line in `routes()`.

Module templates (`src/Modules/*/templates/*.html`) are **fragments**, not full documents: no `<html>/<head>/<body>`, no jQuery/Fomantic CDN includes (those are loaded once, globally, by `templates/index.phtml`). A fragment may pull its own extra CDN dependency inline if it needs something the base layout doesn't provide (e.g. the WireGuard configs template loads `qrcode-generator` itself). Fragments currently embed their "future data" as a `<script type="application/json">` stub block plus client-side JS that renders it — there is no server-side data injection yet, so don't assume `render()` receives or needs any arguments to produce real data.

## Base layout & frontend stack

`templates/index.phtml` provides the shared chrome: a fixed Fomantic UI (Semantic UI fork) inverted menu built from the same route paths used in `src/router.php` (kept in sync by hand, not generated from `routes()`), a `ui main container` that injects `$moduleContent` raw, and a footer. jQuery + Fomantic UI are loaded once here via CDN (`jquery@3.7.1`, `fomantic-ui@2.9.4`) — module fragments should rely on these rather than re-including them.

Current routes (`src/router.php`): `/` and `/wireguard/configs` -> `WireGuardConfigsModule`; `/wireguard` -> `WireGuardStatusModule`; `/firewall` -> `FirewallModule`.
