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
use Twig_Environment;

class PageBlockProvider
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
     * @var  Twig_Environment
     */
    protected $twigEnv;

    /**
     * Creates a new {@link \bitExpert\AddItEasy\Twig\PageBlockProvider}.
     *
     * @param $basePath
     * @param Twig_Environment $twigEnv
     */
    public function __construct($basePath, Twig_Environment $twigEnv)
    {
        $this->basePath = $basePath;
        $this->twigEnv = $twigEnv;
        $this->logger = LoggerFactory::getLogger(__CLASS__);
    }

    /**
     * {{@inheritDoc}}
     */
    public function __invoke(Page $page, $blockname, array $params = [])
    {
        /** @var \Twig_Template $template */
        $template = $this->twigEnv->loadTemplate($page->getRelativeFilePath());

        if(!$template->hasBlock($blockname)) {
            $this->logger->debug(sprintf('Block "%s" not found in Page "%s" source file', $blockname, $page->getName()));
            return '';
        }

        return $template->renderBlock($blockname, $this->twigEnv->mergeGlobals($params));
    }
}
