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

use Twig_Environment;
use Twig_SimpleFunction;

class Extension extends \Twig_Extension
{
    /**
     * @var string
     */
    protected $basePath;
    /**
     * @var  Twig_Environment
     */
    protected $twigEnv;

    /**
     * Creates a new {@link \bitExpert\AddItEasy\Twig\Extension}.
     *
     * @param $basePath
     * @param Twig_Environment $twigEnv
     */
    public function __construct($basePath, Twig_Environment $twigEnv)
    {
        $this->basePath = $basePath;
        $this->twigEnv = $twigEnv;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'EasyTwigExtension';
    }

    /**
     * {@inheritDoc}
     */
    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction('siblings', new SiblingsPageProvider($this->basePath)),
            new Twig_SimpleFunction('children', new ChildrenPageProvider($this->basePath)),
            new Twig_SimpleFunction('pageblock', new PageBlockProvider($this->basePath, $this->twigEnv), ['is_safe' => ['html']])
        ];
    }
}
