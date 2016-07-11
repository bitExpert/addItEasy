<?php

/*
 * This file is part of the addItEasy package.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace bitExpert\AddItEasy;

use bitExpert\Adrenaline\Adrenaline;
use bitExpert\Adroit\Action\Resolver\ContainerActionResolver;
use bitExpert\Adroit\Responder\Resolver\ContainerResponderResolver;
use bitExpert\Disco\Annotations\Bean;
use bitExpert\Disco\Annotations\Configuration;
use bitExpert\Disco\Annotations\Parameter;
use bitExpert\Disco\Annotations\Parameters;
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
     * @Bean
     * @Parameters({
     *     @Parameter({"name" = "app.datadir"}),
     *     @Parameter({"name" = "app.templatedir"})
     * })
     * @return Twig_Environment
     */
    public function twigEnv($datadir = '', $templatedir = '')
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
     * @return \bitExpert\Adroit\Action\Resolver\ContainerActionResolver
     */
    protected function containerActionResolver()
    {
        $beanFactory = \bitExpert\Disco\BeanFactoryRegistry::getInstance();
        return new ContainerActionResolver($beanFactory);
    }

    /**
     * @Bean
     * @return \bitExpert\Adroit\Responder\Resolver\ContainerResponderResolver
     */
    protected function containerResponderResolver()
    {
        $beanFactory = \bitExpert\Disco\BeanFactoryRegistry::getInstance();
        return new ContainerResponderResolver($beanFactory);
    }

    /**
     * @Bean
     * @Parameters({
     *     @Parameter({"name" = "app.datadir"})
     * })
     * @return \bitExpert\Pathfinder\Psr7Router
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    protected function router($datadir = '')
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
     * @Bean
     * @Parameters({
     *     @Parameter({"name" = "app.exportdir"}),
     *     @Parameter({"name" = "app.datadir"}),
     *     @Parameter({"name" = "app.assetdir"})
     * })
     * @return ExportCommand
     */
    protected function exportCommand($exportdir = '', $datadir = '', $assetdir = '')
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
     * @return InitCommand
     */
    protected function initCommand()
    {
        return new InitCommand();
    }

    /**
     * @Bean
     * @Parameters({
     *     @Parameter({"name" = "app.datadir"}),
     *     @Parameter({"name" = "app.defaultpage"})
     * })
     * @return HandlePageAction
     */
    public function handleDefaultRouteAction($datadir = '', $defaultpage = '')
    {
        return new HandleDefaultPageAction($datadir, $defaultpage);
    }

    /**
     * @Bean
     * @Parameters({
     *     @Parameter({"name" = "app.datadir"})
     * })
     * @return HandlePageAction
     */
    public function handlePageAction($datadir = '')
    {
        return new HandlePageAction($datadir);
    }

    /**
     * @Bean
     * @Parameters({
     *     @Parameter({"name" = "site", "default" = "[]"})
     * })
     * @return \bitExpert\AddItEasy\Http\Responder\TwigResponder
     */
    public function renderPage(array $siteParams = [])
    {
        return new TwigResponder($this->twigEnv(), $siteParams);
    }

    /**
     * @Bean
     * @return \bitExpert\Adrenaline\Adrenaline
     */
    public function webapp()
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
     * @return Application
     */
    public function cliapp()
    {
        $app = new Application();
        $app->addCommands([$this->exportCommand(), $this->initCommand()]);
        return $app;
    }
}
