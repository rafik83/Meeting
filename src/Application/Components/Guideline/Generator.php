<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Guideline;

use ScssPhp\ScssPhp\Compiler;
use ScssPhp\ScssPhp\Exception\ParserException;
use ScssPhp\ScssPhp\Formatter\Compressed;
use Proximum\Vimeet\Application\Exception\Asset\GuidelineAssetBuildFailedException;
use Proximum\Vimeet\Domain\Model\Event;

class Generator
{
    /** @var string */
    private $webAssetsPath;

    /** @var \Twig_Environment */
    private $twig;

    /** @var string */
    private $bundleGuidelinePath;

    /** @var string */
    private $fontPath;

    /** @var string */
    private $imagePath;

    /** @var string */
    private $rootPath;

    /**
     * @param \Twig_Environment $twig
     * @param string            $rootPath
     * @param string            $webAssetsPath
     * @param string            $bundleGuidelinePath
     * @param string            $fontPath
     * @param string            $imagePath
     */
    public function __construct(
        \Twig_Environment $twig,
        $rootPath,
        $webAssetsPath,
        $bundleGuidelinePath,
        $fontPath,
        $imagePath
    ) {
        $this->twig                = $twig;
        $this->rootPath            = $rootPath;
        $this->webAssetsPath       = $webAssetsPath;
        $this->bundleGuidelinePath = $bundleGuidelinePath;
        $this->fontPath            = $fontPath;
        $this->imagePath           = $imagePath;
    }

    /**
     * @param Event $event
     *
     * @throws GuidelineAssetBuildFailedException
     *
     * @return string
     */
    public function generate(Event $event)
    {
        $gradientLeftColor  = $event->getConfiguration()->getLeftColor();
        $gradientRightColor = $event->getConfiguration()->getRightColor();
        $gradientHeaderLeftColor  = $event->getConfiguration()->getHeaderLeftColor();
        $gradientHeaderRightColor = $event->getConfiguration()->getHeaderRightColor();
        $colorHighlighted   = $event->getConfiguration()->getTextColor();
        $gradientHeaderButtonLeftColor = $event->getConfiguration()->getHeaderButtonLeftColor();
        $gradientHeaderButtonRightColor = $event->getConfiguration()->getHeaderButtonRightColor();
        $gradientHeaderButtonTextColor = $event->getConfiguration()->getHeaderButtonTextColor();

        $repoName = $event->getId();

        $this->createDirIfNotExist($this->rootPath . DIRECTORY_SEPARATOR . $this->webAssetsPath);

        $fullPath = $this->rootPath . DIRECTORY_SEPARATOR . $this->webAssetsPath . DIRECTORY_SEPARATOR . $repoName;

        $this->createDirIfNotExist($fullPath);

        $file = $this->twig->loadTemplate('AdminBundle:Asset:eventGuidelineVars.scss.twig')->render([
            'gradientLeftColor' => $gradientLeftColor,
            'gradientRightColor' => $gradientRightColor,
            'gradientHeaderLeftColor' => $gradientHeaderLeftColor,
            'gradientHeaderRightColor' => $gradientHeaderRightColor,
            'gradientHeaderButtonLeftColor' => $gradientHeaderButtonLeftColor,
            'gradientHeaderButtonRightColor' => $gradientHeaderButtonRightColor,
            'gradientHeaderButtonTextColor' => $gradientHeaderButtonTextColor,
            'colorHighLighted' => $colorHighlighted,
            'bundleGuidelinePath' => $this->bundleGuidelinePath,
            'fontPath' => $this->fontPath,
            'imagePath' => $this->imagePath,
            'backgroundImage' => $event->getConfiguration()->getBackgroundImage(),
            'backgroundColor' => $event->getConfiguration()->getBackgroundColor(),
        ]);

        $varsFileName = 'vars-' . sha1(uniqid()) . '.scss';
        $mainFileName = 'main-' . sha1(uniqid()) . '.css';

        $this->put($fullPath . DIRECTORY_SEPARATOR . $varsFileName, $file);

        $scss = new Compiler();

        $scss->setFormatter(Compressed::class);

        try {
            $cssOut = $scss->compile(file_get_contents($fullPath . DIRECTORY_SEPARATOR . $varsFileName));
            $this->put($fullPath . DIRECTORY_SEPARATOR . $mainFileName, $cssOut);
        } catch (ParserException $ex) {
            throw new GuidelineAssetBuildFailedException($ex->getMessage());
        }

        $this->removeOldFiles($fullPath, $mainFileName);

        return $this->webAssetsPath . DIRECTORY_SEPARATOR . $repoName . DIRECTORY_SEPARATOR . $mainFileName;
    }

    /**
     * @param string $path
     */
    private function createDirIfNotExist($path)
    {
        if (!file_exists($path) && !is_dir($path)) {
            $this->mkdir($path);
        }
    }

    /**
     * @param string $path
     * @param string $file
     */
    public function removeOldFiles($path, $file)
    {
        // Delete all scss files
        foreach (glob($path . DIRECTORY_SEPARATOR . '*.scss') as $filename) {
            $this->unlink($filename);
        }

        // Delete old css files
        foreach (glob($path . DIRECTORY_SEPARATOR . '*.css') as $filename) {
            if ($filename !== ($path . DIRECTORY_SEPARATOR . $file)) {
                $this->unlink($filename);
            }
        }
    }

    /**
     * @param string $filename
     *
     * @throws GuidelineAssetBuildFailedException
     */
    private function unlink($filename)
    {
        if (false === @unlink($filename)) {
            throw new GuidelineAssetBuildFailedException(sprintf('Unable to unlink "%s"', $filename), 0, $this->createLastErrorException());
        }
    }

    /**
     * @param string $path
     *
     * @throws GuidelineAssetBuildFailedException
     */
    private function mkdir($path)
    {
        if (false === @mkdir($path)) {
            throw new GuidelineAssetBuildFailedException(sprintf('Unable to mkdir "%s"', $path), 0, $this->createLastErrorException());
        }
    }

    /**
     * @param string $filename
     * @param string $contents
     *
     * @throws GuidelineAssetBuildFailedException
     */
    private function put($filename, $contents)
    {
        if (false === @file_put_contents($filename, $contents)) {
            throw new GuidelineAssetBuildFailedException(sprintf('Unable to put contents in "%s"', $filename), 0, $this->createLastErrorException());
        }
    }

    /**
     * @return GuidelineAssetBuildFailedException
     */
    private function createLastErrorException()
    {
        return new GuidelineAssetBuildFailedException(print_r(error_get_last(), true));
    }
}
