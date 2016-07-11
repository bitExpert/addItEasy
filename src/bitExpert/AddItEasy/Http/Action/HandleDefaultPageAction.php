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

class HandleDefaultPageAction extends AbstractAction
{
    /**
     * @var LoggerInterface
     */
    protected $logger;
    /**
     * @var string
     */
    protected $basePath;
    protected $defaultPage;

    /**
     * Creates a new {@link \bitExpert\AddItEasy\Http\Action\HandleDefaultPageAction}.
     *
     * @param string $basePath
     * @param string $defaultPage
     */
    public function __construct($basePath, $defaultPage)
    {
        $this->basePath = $basePath;
        $this->defaultPage = $defaultPage;
        $this->logger = LoggerFactory::getLogger(__CLASS__);
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response)
    {
        try {
            $page = new Page($this->basePath, $this->defaultPage);

            return $response
                ->withStatus(301)
                ->withHeader('Location', '/'.$page->getName());
        } catch (\Exception $e) {
            $this->logger->debug(sprintf('Page creation failed, due to "%s"', $e->getMessage()));
        }

        return $response->withStatus(404);
    }
}
