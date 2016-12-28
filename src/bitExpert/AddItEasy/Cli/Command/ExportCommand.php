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

namespace bitExpert\AddItEasy\Cli\Command;

use bitExpert\Adrenaline\Adrenaline;
use bitExpert\AddItEasy\Domain\Page;
use bitExpert\Slf4PsrLog\LoggerFactory;
use Psr\Log\LoggerInterface;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequestFactory;
use Zend\Diactoros\Uri;

class ExportCommand extends Command
{
    /**
     * @var LoggerInterface
     */
    protected $logger;
    /**
     * @var Adrenaline
     */
    protected $app;
    /**
     * @var string
     */
    protected $exportDir;
    /**
     * @var string
     */
    protected $dataDir;
    /**
     * @var string
     */
    protected $assetDir;

    /**
     * Creates a new {@link \bitExpert\AddItEasy\Cli\Command\ExportCommand}.
     *
     * @param Adrenaline $app
     * @param string $exportDir
     * @param string $dataDir
     * @param string $assetDir
     */
    public function __construct(Adrenaline $app, $exportDir, $dataDir, $assetDir)
    {
        parent::__construct('export');
        $this->setDescription('Runs the static site export process');

        $this->app = $app;
        $this->exportDir = realpath($exportDir);
        $this->dataDir = realpath($dataDir);
        $this->assetDir = realpath($assetDir);
        $this->logger = LoggerFactory::getLogger(__CLASS__);
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $page = null;
        $pages = glob($this->dataDir . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR);
        if (!isset($pages[0])) {
            $output->writeln('No pages found!');
            return;
        }

        $pagename = preg_replace('/([0-9]+[\-])/', '', basename($pages[0]));
        try {
            $page = new Page($this->dataDir, $pagename);
        } catch (\Exception $e) {
            $output->writeln(sprintf('Could not instantiate page "%s"', $pagename));
            return;
        }

        foreach ($page->getSiblings() as $sibling) {
            $this->recursivlyExportPages($sibling, $output);
        }

        $assetBaseDir = basename($this->assetDir);
        $assertExportDir = $this->exportDir . DIRECTORY_SEPARATOR . $assetBaseDir;
        $assets = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->assetDir),
            RecursiveIteratorIterator::SELF_FIRST
        );
        foreach($assets as $sourceAssetFile){
            $sourceAssetFile = realpath($sourceAssetFile);
            if (false === strpos($sourceAssetFile, $this->assetDir) || is_dir($sourceAssetFile)) {
                continue;
            }

            $exportAssetFile = $assertExportDir . DIRECTORY_SEPARATOR . str_replace($this->assetDir, '', $sourceAssetFile);
            $exportAssetDir = dirname($exportAssetFile);
            if (!is_dir($exportAssetDir)) {
                if (!mkdir($exportAssetDir, 0755, true) && !is_dir($exportAssetDir)) {
                    throw new \RuntimeException(sprintf('Could not create directory "%s"', $exportAssetDir));
                }
            }

            $output->writeln(sprintf('Exporting asset "%s"', $sourceAssetFile));
            file_put_contents($exportAssetFile, file_get_contents($sourceAssetFile));
        }
    }

    /**
     * Helper method to recursivly export the children of the given $page.
     *
     * @param Page $page
     * @param OutputInterface $output
     */
    protected function recursivlyExportPages(Page $page, OutputInterface $output)
    {
        $this->exportPage($page, $output);

        foreach($page->getChildren() as $child) {
            $this->recursivlyExportPages($child, $output);
        }
    }

    /**
     * Exports a page by firing a request against the Adrenaline middleware.
     *
     * @param Page $page
     * @param OutputInterface $output
     */
    protected function exportPage(Page $page, OutputInterface $output)
    {
        $output->write(sprintf('Exporting page %s ', $page->getName()));
        try {
            $request = ServerRequestFactory::fromGlobals();
            $response = new Response();
            $app = $this->app;

            $request = $request->withUri(new Uri('/' . $page->getName()));
            $app($request, $response);
            $output->writeln('OK');
        } catch (\Exception $e) {
            $output->writeln('FAILED!');
            $this->logger->debug(sprintf('Export for "%s" failed: %s', $page->getName(), $e->getMessage()));
        }
    }
}
