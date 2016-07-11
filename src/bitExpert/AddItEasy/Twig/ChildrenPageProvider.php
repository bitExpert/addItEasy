<?php

/*
 * This file is part of the addItEasy package.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace bitExpert\AddItEasy\Twig;

use bitExpert\AddItEasy\Domain\Page;
use bitExpert\Slf4PsrLog\LoggerFactory;
use Psr\Log\LoggerInterface;

class ChildrenPageProvider
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
     * Creates a new {@link \bitExpert\AddItEasy\Twig\ChildrenPageProvider}.
     *
     * @param $basePath
     */
    public function __construct($basePath)
    {
        $this->basePath = $basePath;
        $this->logger = LoggerFactory::getLogger(__CLASS__);
    }

    /**
     * {{@inheritDoc}}
     */
    public function __invoke($pageName)
    {
        try {
            return (new Page($this->basePath, $pageName))->getChildren();
        } catch(\InvalidArgumentException $e) {
            $this->logger->debug($e->getMessage());
        }

        return [];
    }
}
