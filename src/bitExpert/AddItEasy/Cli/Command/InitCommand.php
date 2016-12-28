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

use bitExpert\Slf4PsrLog\LoggerFactory;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InitCommand extends Command
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Creates a new {@link \bitExpert\AddItEasy\Cli\Command\InitCommand}.
     */
    public function __construct()
    {
        parent::__construct('init');
        $this->setDescription('Creates initial filesystem layout for a new project');

        $this->logger = LoggerFactory::getLogger(__CLASS__);
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $workingDir = getcwd();
        $directoryStructure = [
            'assets' => [],
            'cache' => [],
            'config' => ['config.inc.php' => $this->getConfigFileTemplate()],
            'content' => [],
            'export' => [],
            'template' => [],
            'index.php' => $this->getFrontControllerTemplate(),
            '.htaccess' => $this->getHtAccessTemplate()
        ];

        $this->createDirectoryStructure($workingDir, $directoryStructure);
    }

    /**
     * Helper method to recursivly recreate the defaule file and folder structure needed.
     *
     * @param $workingDir
     * @param array $directoryStructure
     * @return bool
     * @throws \RuntimeException
     */
    protected function createDirectoryStructure($workingDir, array $directoryStructure)
    {
        foreach ($directoryStructure as $name => $content) {
            if (is_array($content)) {
                $directoryToCreate = $workingDir . DIRECTORY_SEPARATOR . $name;
                echo $directoryToCreate . "\n";
                if (!mkdir($directoryToCreate, 0755, true) && !is_dir($directoryToCreate)) {
                    throw new \RuntimeException(sprintf('Could not create directory "%s"', $directoryToCreate));
                }
                $this->createDirectoryStructure($directoryToCreate, $content);
            } else if (is_string($content)) {
                $fileToCreate = $workingDir . DIRECTORY_SEPARATOR . $name;
                echo $fileToCreate . "\n";
                if (false === file_put_contents($fileToCreate, $content)) {
                    throw new \RuntimeException(sprintf('Could not create file "%s"', $fileToCreate));
                }
            }
        }

        return true;
    }

    /**
     * Helper method to return the content of the default configuration file.
     *
     * @return string
     */
    protected function getConfigFileTemplate()
    {
        $content = '<?php' . PHP_EOL;
        $content .= PHP_EOL;
        $content .= '$EASY_CONF = [];' . PHP_EOL;
        $content .= PHP_EOL;
        $content .= '// addITeasy configuration' . PHP_EOL;
        $content .= '$EASY_CONF["app"] = [];' . PHP_EOL;
        $content .= '$EASY_CONF["app"]["cachedir"] = __DIR__ . "/../cache";' . PHP_EOL;
        $content .= '$EASY_CONF["app"]["assetdir"] = __DIR__ . "/../assets";' . PHP_EOL;
        $content .= '$EASY_CONF["app"]["logfile"] = $EASY_CONF["app"]["cachedir"] . "/addITeasy.log";' . PHP_EOL;
        $content .= '$EASY_CONF["app"]["datadir"] = __DIR__ . "/../content";' . PHP_EOL;
        $content .= '$EASY_CONF["app"]["templatedir"] = __DIR__ . "/../template/";' . PHP_EOL;
        $content .= '$EASY_CONF["app"]["exportdir"] = __DIR__ . "/../export";' . PHP_EOL;
        $content .= '$EASY_CONF["app"]["defaultpage"] = "";' . PHP_EOL;
        $content .= PHP_EOL;
        $content .= '// Site configuration (these variables will get passed to the twig template)' . PHP_EOL;
        $content .= '$EASY_CONF["site"][] = "";' . PHP_EOL;
        $content .= '$EASY_CONF["site"]["title"] = "addITeasy";' . PHP_EOL;
        return $content;
    }

    /**
     * Helper method to return the content of the front controller file.
     *
     * @return string
     */
    protected function getFrontControllerTemplate()
    {
        $content = '<?php' . PHP_EOL;
        $content .= 'require __DIR__ . "/vendor/autoload.php";' . PHP_EOL;
        $content .= 'require __DIR__ . "/config/config.inc.php";' . PHP_EOL;
        $content .= '// bootstrap and run addITeasy' . PHP_EOL;
        $content .= 'require __DIR__ . "/vendor/bitexpert/additeasy/src/bootstrap.php";' . PHP_EOL;
        return $content;
    }

    /**
     * Helper method to return the default content of the .htaccess file.
     *
     * @return string
     */
    protected function getHtAccessTemplate()
    {
        $content = '<IfModule mod_rewrite.c>' . PHP_EOL;
        $content .= '    SetEnv HTTP_MOD_REWRITE On' . PHP_EOL;
        $content .= '    RewriteEngine On' . PHP_EOL;
        $content .= PHP_EOL;
        $content .= '    RewriteCond %{REQUEST_FILENAME} -s [OR]' . PHP_EOL;
        $content .= '    RewriteCond %{REQUEST_FILENAME} -l [OR]' . PHP_EOL;
        $content .= '    RewriteCond %{REQUEST_FILENAME} -d' . PHP_EOL;
        $content .= '    RewriteRule ^.*$ - [NC,L]' . PHP_EOL;
        $content .= PHP_EOL;
        $content .= '    RewriteCond %{REQUEST_URI}::$1 ^(/.+)(.+)::\2$' . PHP_EOL;
        $content .= '    RewriteRule ^(.*) - [E=BASE:%1]' . PHP_EOL;
        $content .= '    RewriteRule ^(.*)$ %{ENV:BASE}index.php [NC,L]' . PHP_EOL;
        $content .= '</IfModule>' . PHP_EOL;
        return $content;
    }
}
