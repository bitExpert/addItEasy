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

namespace bitExpert\AddItEasy\Export;

use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Response\EmitterInterface;

class FileEmitter implements EmitterInterface
{
    /**
     * @var string
     */
    protected $exportDir;

    /**
     * Creates a new {@link \bitExpert\AddItEasy\Export\FileEmitter}.
     *
     * @param string $exportDir
     */
    public function __construct($exportDir)
    {
        $this->exportDir = $exportDir;
    }

    /**
     * {@inheritdoc}
     * @throws \RuntimeException
     */
    public function emit(ResponseInterface $response)
    {
        $headers = $response->getHeader('X-Easy-Name');
        if (count($headers) === 0) {
            return;
        }

        $pageName = $headers[0] ?: '';
        if (empty($pageName)) {
            return;
        }

        $pageDir = sprintf('%s/%s', $this->exportDir, $pageName);
        $pageFile = sprintf('%s/index.html', $pageDir);
        if (!is_dir($pageDir)) {
            if (!@mkdir($pageDir, 0777, true) && !is_dir($pageDir)) {
                throw new \RuntimeException(sprintf('Cannot create directory "%s"', $pageDir));
            }
        }

        $response->getBody()->rewind();
        file_put_contents($pageFile, $response->getBody()->getContents());
    }
}
