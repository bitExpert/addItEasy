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

namespace bitExpert\AddItEasy\Http\Middleware;

use bitExpert\Pathfinder\RoutingResult;
use bitExpert\Slf4PsrLog\LoggerFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Zend\Stratigility\MiddlewareInterface;

class PageNotFoundMiddleware implements MiddlewareInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Creates a new {@link \bitExpert\AddItEasy\Http\Middleware\PageNotFoundMiddleware}.
     */
    public function __construct()
    {
        $this->logger = LoggerFactory::getLogger(__CLASS__);
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $out = null)
    {
        /** @var RoutingResult $routingResult */
        $routingResult = $request->getAttribute(RoutingResult::class, null);
        if (($routingResult === null) || $routingResult->failed()) {
            $this->logger->debug('No matching page found. Returning 404.');
            return $response->withStatus(404);
        }

        return $out($request, $response);
    }
}
