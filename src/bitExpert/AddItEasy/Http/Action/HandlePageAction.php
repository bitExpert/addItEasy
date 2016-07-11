<?php

/*
 * This file is part of the addItEasy package.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace bitExpert\AddItEasy\Http\Action;

use bitExpert\Adrenaline\Action\AbstractAction;
use bitExpert\AddItEasy\Domain\Page;
use bitExpert\Slf4PsrLog\LoggerFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

class HandlePageAction extends AbstractAction
{
    /**
     * @var LoggerInterface
     */
    protected $logger;
    /**
     * @var string
     */
    protected $basePath;

    /**
     * Creates a new {@link \bitExpert\AddItEasy\Http\Action\HandlePageAction}.
     *
     * @param $basePath
     */
    public function __construct($basePath)
    {
        $this->basePath = $basePath;
        $this->logger = LoggerFactory::getLogger(__CLASS__);
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response)
    {
        $page = null;
        $queryParams = $request->getQueryParams();
        $pageName = $queryParams['page'] ?: '';

        try {
            $page = new Page($this->basePath, $pageName);
        } catch (\Exception $e) {
            $this->logger->debug(sprintf('Page creation failed, due to "%s"', $e->getMessage()));
        }

        return $this->createPayload('RenderPage', ['page' => $page]);
    }
}
