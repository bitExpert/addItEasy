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

namespace bitExpert\AddItEasy;

use bitExpert\Adrenaline\Adrenaline;
use bitExpert\Adroit\Action\Resolver\ContainerActionResolver;
use bitExpert\Adroit\Responder\Resolver\ContainerResponderResolver;
use bitExpert\Disco\Annotations\Bean;
use bitExpert\Disco\Annotations\Configuration;
use bitExpert\Disco\Annotations\Parameter;
use bitExpert\AddItEasy\Cli\Command\ExportCommand;
use bitExpert\AddItEasy\Cli\Command\InitCommand;
use bitExpert\AddItEasy\Export\FileEmitter;
use bitExpert\AddItEasy\Http\Action\HandleDefaultPageAction;
use bitExpert\AddItEasy\Http\Action\HandlePageAction;
use bitExpert\AddItEasy\Http\Middleware\PageNotFoundMiddleware;
use bitExpert\AddItEasy\Http\Responder\TwigResponder;
use bitExpert\AddItEasy\Http\Router\Matcher\PageExistsMatcher;
use bitExpert\AddItEasy\Http\Router\Psr7Router;
use bitExpert\AddItEasy\Twig\Extension;
use bitExpert\Disco\BeanFactoryRegistry;
use bitExpert\Pathfinder\RouteBuilder;
use Symfony\Component\Console\Application;
use Twig_Environment;
use Twig_Extension_Debug;
use Twig_Loader_Filesystem;

/**
 * @Configuration
 */
class Config
{
    /**
     * @Bean({
     *   "parameters"={
     *     @Parameter({"name" = "app.datadir"}),
     *     @Parameter({"name" = "app.templatedir"})
     *   }
     * })
     */
    public function twigEnv($datadir = '', $templatedir = '') : Twig_Environment
    {
        $twigEnv = new Twig_Environment(
            new Twig_Loader_Filesystem([$datadir, $templatedir]),
            ['debug' => true]
        );
        $twigEnv->addExtension(new Twig_Extension_Debug());
        $twigEnv->addExtension(new Extension($datadir, $twigEnv));

        return $twigEnv;
    }

    /**
     * @Bean
     * @throws \RuntimeException
     */
    protected function containerActionResolver() : ContainerActionResolver
    {
        $beanFactory = BeanFactoryRegistry::getInstance();
        return new ContainerActionResolver($beanFactory);
    }

    /**
     * @Bean
     * @throws \RuntimeException
     */
    protected function containerResponderResolver() : ContainerResponderResolver
    {
        $beanFactory = BeanFactoryRegistry::getInstance();
        return new ContainerResponderResolver($beanFactory);
    }

    /**
     * @Bean({
     *   "parameters"={
     *     @Parameter({"name" = "app.datadir"})
     *   }
     * })
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    protected function router($datadir = '') : Psr7Router
    {
        $defaultRoute = RouteBuilder::route()
            ->get('/')
            ->to('handleDefaultRouteAction')
            ->build();
        $pageRoute = RouteBuilder::route()
            ->get('/[:page]')
            ->to('handlePageAction')
            ->ifMatches('page', new PageExistsMatcher($datadir))
            ->build();

        return new Psr7Router([$defaultRoute, $pageRoute]);
    }

    /**
     * @Bean({
     *   "parameters"={
     *     @Parameter({"name" = "app.exportdir"}),
     *     @Parameter({"name" = "app.datadir"}),
     *     @Parameter({"name" = "app.assetdir"})
     *   }
     * })
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    protected function exportCommand($exportdir = '', $datadir = '', $assetdir = '') : ExportCommand
    {
        $app = new Adrenaline(
            [$this->containerActionResolver()],
            [$this->containerResponderResolver()],
            $this->router(),
            new FileEmitter($exportdir)
        );

        $app->beforeResolveAction(new PageNotFoundMiddleware());

        return new ExportCommand($app, $exportdir, $datadir, $assetdir);
    }

    /**
     * @Bean
     */
    protected function initCommand() : InitCommand
    {
        return new InitCommand();
    }

    /**
     * @Bean({
     *   "parameters"={
     *     @Parameter({"name" = "app.datadir"}),
     *     @Parameter({"name" = "app.defaultpage"})
     *   }
     * })
     */
    public function handleDefaultRouteAction($datadir = '', $defaultpage = '') : HandleDefaultPageAction
    {
        return new HandleDefaultPageAction($datadir, $defaultpage);
    }

    /**
     * @Bean({
     *   "parameters"={
     *     @Parameter({"name" = "app.datadir"})
     *   }
     * })
     */
    public function handlePageAction($datadir = ''): HandlePageAction
    {
        return new HandlePageAction($datadir);
    }

    /**
     * @Bean({
     *   "parameters"={
     *     @Parameter({"name" = "site", "default" = "[]"})
     *   }
     * })
     */
    public function renderPage(array $siteParams = []) : TwigResponder
    {
        return new TwigResponder($this->twigEnv(), $siteParams);
    }

    /**
     * @Bean
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function webapp() : Adrenaline
    {
        $app = new Adrenaline(
            [$this->containerActionResolver()],
            [$this->containerResponderResolver()],
            $this->router()
        );

        $app->beforeResolveAction(new PageNotFoundMiddleware());

        return $app;
    }

    /**
     * @Bean
     */
    public function cliapp() : Application
    {
        $app = new Application();
        $app->addCommands([$this->exportCommand(), $this->initCommand()]);
        return $app;
    }
}
