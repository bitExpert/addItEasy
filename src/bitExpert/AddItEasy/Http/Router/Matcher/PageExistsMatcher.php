<?php

/*
 * This file is part of the addItEasy package.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace bitExpert\AddItEasy\Http\Router\Matcher;

use bitExpert\AddItEasy\Domain\Page;
use bitExpert\Pathfinder\Matcher\Matcher;
use bitExpert\Slf4PsrLog\LoggerFactory;
use Psr\Log\LoggerInterface;

class PageExistsMatcher implements Matcher
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
     * Creates a new {@link \bitExpert\AddItEasy\Http\Router\Matcher\PageExistsMatcher}.
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
    public function __invoke($value)
    {
        try {
            new Page($this->basePath, $value);
            return true;
        } catch (\InvalidArgumentException $e) {
            $this->logger->debug($e->getMessage());
        }

        return false;
    }
}
