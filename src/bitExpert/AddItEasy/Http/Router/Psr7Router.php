<?php

/*
 * This file is part of the addItEasy package.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types = 1);

namespace bitExpert\AddItEasy\Http\Router;

use bitExpert\Pathfinder\Route;

class Psr7Router extends \bitExpert\Pathfinder\Psr7Router
{
    /**
     * {@inheritdoc}
     */
    protected function getPathMatcherForRoute(Route $route) : string
    {
        $pathMatcher = preg_replace('#\[:(.+?)\]#i', '(?P<$1>.+?)/?', $route->getPath());
        return sprintf('#^%s$#i', $pathMatcher);
    }
}
