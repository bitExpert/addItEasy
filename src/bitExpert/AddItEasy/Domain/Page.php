<?php

/*
 * This file is part of the addItEasy package.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace bitExpert\AddItEasy\Domain;

class Page
{
    /**
     * @var string
     */
    protected $basePath;
    /**
     * @var string
     */
    protected $name;
    /**
     * @var string
     */
    protected $pagePath;

    /**
     * Creates a new {@link \bitExpert\AddItEasy\Domain\Page}.
     *
     * @param string $basePath
     * @param string $name
     * @throws \InvalidArgumentException
     */
    public function __construct($basePath, $name)
    {
        $basePath = realpath($basePath);
        if (false === $basePath) {
            throw new \InvalidArgumentException(sprintf('Basepath "%s" does not exist!', $basePath));
        }

        $searchName = str_replace(DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR . '*', ltrim($name, DIRECTORY_SEPARATOR));
        $pages = glob($basePath . DIRECTORY_SEPARATOR . '*' . $searchName, GLOB_ONLYDIR);
        if (!isset($pages[0])) {
            throw new \InvalidArgumentException(sprintf('Source folder for page "%s" not found!', $name));
        }
        // page is only valid if a source file or subdirectories were found in the page path...
        $sourceFile = glob($pages[0] . DIRECTORY_SEPARATOR . '*');
        if (!isset($sourceFile[0])) {
            throw new \InvalidArgumentException(sprintf('Source file in folder "%s" for page "%s" not found!', $pages[0], $name));
        }

        $this->basePath = $basePath;
        $this->name = $name;
        $this->pagePath = $pages[0];
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getAbsoluteFilePath()
    {
        $pages = glob($this->pagePath . DIRECTORY_SEPARATOR . '*');
        return $pages[0];
    }

    /**
     * @return string
     */
    public function getRelativeFilePath()
    {
        $absolutePath = $this->getAbsoluteFilePath();
        return str_replace($this->basePath, '', $absolutePath);
    }

    /**
     * @return Page[]
     */
    public function getSiblings()
    {
        $sourceDir = dirname($this->pagePath);
        $relativeSourceDirPath = ltrim(str_replace($this->basePath, '', $sourceDir), DIRECTORY_SEPARATOR);

        foreach (glob($sourceDir . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR) as $sibling) {
            if (is_dir($sibling)) {
                $sibling = pathinfo($sibling, PATHINFO_FILENAME);
                if (preg_match('/^[0-9]+[\-](.*)$/', $sibling, $match)) {
                    $sibling = $match[1];
                    $siblingRelativePath = $relativeSourceDirPath;

                    if (!empty($siblingRelativePath)) {
                        $siblingRelativePath .= DIRECTORY_SEPARATOR;
                    }

                    try {
                        yield new self($this->basePath, preg_replace('/([0-9]+[\-])/', '', $siblingRelativePath . $sibling));
                    } catch (\Exception $e) {
                    }
                }
            }
        }
    }

    /**
     * @return Page[]
     */
    public function getChildren()
    {
        $sourceDir = $this->pagePath;
        $relativeSourceDirPath = ltrim(str_replace($this->basePath, '', $sourceDir), DIRECTORY_SEPARATOR);

        foreach (glob($sourceDir . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR) as $child) {
            if (is_dir($child)) {
                $child = pathinfo($child, PATHINFO_FILENAME);
                if (preg_match('/^[0-9]+[\-](.*)$/', $child, $match)) {
                    $child = $match[1];
                    $childRelativePath = $relativeSourceDirPath;

                    if (!empty($childRelativePath)) {
                        $childRelativePath .= DIRECTORY_SEPARATOR;
                    }

                    try {
                        yield new self($this->basePath, preg_replace('/([0-9]+[\-])/', '', $childRelativePath . $child));
                    } catch (\Exception $e) {
                    }
                }
            }
        }
    }

    /**
     * @return Page|null
     */
    public function getParent()
    {
        $parentPath = dirname($this->pagePath);
        if (false !== strpos($parentPath, $this->basePath)) {
            $parentName = basename($parentPath);
            if (preg_match('/^[0-9]+[\-](.*)$/', $parentName, $match)) {
                $parentName = $match[1];
                return new self($this->basePath, $parentName);
            }
        }

        return null;
    }
}
