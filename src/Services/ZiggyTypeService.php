<?php

namespace Kiwilan\Steward\Services;

use Illuminate\Support\Facades\File;
use Tightenco\Ziggy\Ziggy;

class ZiggyTypeService
{
    protected function __construct()
    {
    }

    public static function make(): self
    {
        $path = config('steward.typescript.path') ?? resource_path('js');
        $filename = config('steward.typescript.file.ziggy') ?? 'types-ziggy.d.ts';

        $service = new self();

        $file = "{$path}/{$filename}";
        $generatedRoutes = $service->generate();

        $service->makeDirectory($path);
        File::put($file, $generatedRoutes);

        return $service;
    }

    private function generate(): string
    {
        $ziggy = (new Ziggy(false, null));

        $routes = collect($ziggy->toArray()['routes'])
            ->map(function ($route, $key) {
                $methods = json_encode($route['methods'] ?? []);

                return "  '{$key}': { 'uri': '{$route['uri']}', 'methods': {$methods} }";
            })
            ->join("\n")
        ;

        $content = '';

        $content .= '// This file is auto generated by GenerateTypeCommand.'.PHP_EOL;
        $content .= 'import {Config, InputParams, Router, RouteParamsWithQueryOverload} from "ziggy-js";'.PHP_EOL.PHP_EOL;
        $content .= 'declare type LaravelRoutes = {'.PHP_EOL;
        $content .= $routes.PHP_EOL;
        $content .= '};'.PHP_EOL.PHP_EOL;

        $content .= 'export interface IPage {'.PHP_EOL;
        $content .= '  props: {'.PHP_EOL;
        $content .= '    user: App.Models.User'.PHP_EOL;
        $content .= '    jetstream: { canCreateTeams: boolean, hasTeamFeatures: boolean, managesProfilePhotos: boolean, hasApiFeatures: boolean }'.PHP_EOL;
        $content .= '  }'.PHP_EOL;
        $content .= '}'.PHP_EOL.PHP_EOL;

        $content .= 'declare global {'.PHP_EOL;
        $content .= '  declare interface ZiggyLaravelRoutes extends LaravelRoutes {}'.PHP_EOL;
        $content .= '}'.PHP_EOL;

        $content .= 'declare module "@vue/runtime-core" {'.PHP_EOL;
        $content .= '  interface ComponentCustomProperties {'.PHP_EOL;
        $content .= '    $route: (name: keyof ZiggyLaravelRoutes, params?: RouteParamsWithQueryOverload | RouteParam, absolute?: boolean, customZiggy?: Config) => string;'.PHP_EOL;
        $content .= '    $isRoute: (name: keyof ZiggyLaravelRoutes, params?: RouteParamsWithQueryOverload) => boolean;'.PHP_EOL;
        $content .= '    $currentRoute: () => string;'.PHP_EOL;
        $content .= '    $page: IPage'.PHP_EOL;
        $content .= '  }'.PHP_EOL;
        $content .= '}'.PHP_EOL;

        $content .= 'export { LaravelRoutes };'.PHP_EOL;

        return $content;
    }

    protected function makeDirectory($path)
    {
        if (! File::isDirectory(dirname(base_path($path)))) {
            File::makeDirectory(dirname(base_path($path)), 0755, true, true);
        }

        return $path;
    }
}
