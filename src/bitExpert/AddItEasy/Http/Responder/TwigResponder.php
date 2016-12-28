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

namespace bitExpert\AddItEasy\Http\Responder;

use bitExpert\Adrenaline\Domain\DomainPayload;
use bitExpert\Adrenaline\Responder\Responder;
use bitExpert\Adroit\Domain\Payload;
use bitExpert\AddItEasy\Domain\Page;
use bitExpert\Slf4PsrLog\LoggerFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Responder to convert a given Twig template into an response object.
 *
 * @api
 */
class TwigResponder implements Responder
{
    /**
     * @var LoggerInterface
     */
    protected $logger;
    /**
     * @var \Twig_Environment
     */
    protected $twig;
    /**
     * @var array
     */
    protected $siteParams;

    /**
     * Creates a new {\bitExpert\AddItEasy\Http\Responder\TwigResponder}.
     *
     * @param \Twig_Environment $twig
     * @param array $siteParams key/value collection of default global variables
     */
    public function __construct(\Twig_Environment $twig, array $siteParams)
    {
        $this->twig = $twig;
        $this->siteParams = $siteParams;
        $this->logger = LoggerFactory::getLogger(__CLASS__);
    }

    /**
     * @inheritdoc
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \Twig_Error_Syntax
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     */
    public function __invoke(Payload $payload, ResponseInterface $response) : ResponseInterface
    {
        /** @var DomainPayload $payload */
        /** @var Page $page */
        $page = $payload->getValue('page', null);
        $status = $payload->getStatus() ?: 200;

        try {
            $response->getBody()->rewind();
            $response->getBody()->write(
                $this->twig->render($page->getRelativeFilePath(), ['page' => $page, 'site' => $this->siteParams])
            );
        } catch (\Exception $e) {
            $this->logger->debug(sprintf('Twig rendering failed, due to "%s"', $e->getMessage()));
        }

        $response = $response->withHeader('Content-Type', 'text/html');
        $response = $response->withHeader('X-Easy-Name', $page->getName());
        return $response->withStatus($status);
    }
}
