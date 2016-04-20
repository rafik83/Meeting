<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Guideline;

use Behat\Transliterator\Transliterator;
use Leafo\ScssPhp\Compiler;
use Leafo\ScssPhp\Exception\ParserException;
use Leafo\ScssPhp\Formatter\Compressed;
use Proximum\Vimeet\Application\Exception\Asset\GuidelineAssetBuildFailedException;
use Proximum\Vimeet\Domain\Model\Event;

class Generator
{
    /**
     * @var string
     */
    private $rootPath;

    /**
     * @var string
     */
    private $webAssetsPath;

    /**
     * @var \Twig_Environment
     */
    private $twig;

    /**
     * @var string
     */
    private $bundleGuidelinePath;

    /**
     * @var string
     */
    private $fontPath;

    /**
     * @param \Twig_Environment $twig
     * @param string            $rootPath
     * @param string            $webAssetsPath
     * @param string            $bundleGuidelinePath
     * @param string            $fontPath
     */
    public function __construct(
        \Twig_Environment $twig,
        $rootPath,
        $webAssetsPath,
        $bundleGuidelinePath,
        $fontPath
    ) {
        $this->rootPath            = $rootPath;
        $this->webAssetsPath       = $webAssetsPath;
        $this->twig                = $twig;
        $this->bundleGuidelinePath = $bundleGuidelinePath;
        $this->fontPath            = $fontPath;
    }

    /**
     * @param Event $event
     *
     * @return string
     * @throws GuidelineAssetBuildFailedException
     */
    public function generate(Event $event)
    {
        $gradientLeftColor  = $event->getConfiguration()->getLeftColor();
        $gradientRightColor = $event->getConfiguration()->getRightColor();
        $colorHighlighted   = $event->getConfiguration()->getTextColor();

        $repoName = Transliterator::urlize($event->getTitle());

        $this->createDirIfNotExist($this->webAssetsPath);

        $fullPath = $this->webAssetsPath . '/' . $repoName;

        $this->createDirIfNotExist($fullPath);

        $file = $this->twig->loadTemplate('AdminBundle:Asset:eventGuidelineVars.scss.twig')->render([
            'gradientLeftColor'   => $gradientLeftColor,
            'gradientRightColor'  => $gradientRightColor,
            'colorHighLighted'    => $colorHighlighted,
            'bundleGuidelinePath' => $this->bundleGuidelinePath,
            'fontPath'            => $this->fontPath,
        ]);

        $varsFileName = 'vars-' . sha1(uniqid()) . '.scss';
        $mainFileName = 'main-' . sha1(uniqid()) . '.css';

        file_put_contents($fullPath . '/' . $varsFileName, $file);

        $scss = new Compiler();

        $scss->setFormatter(Compressed::class);

        try {
            $cssOut = $scss->compile(file_get_contents($fullPath . '/' . $varsFileName));
            file_put_contents($fullPath . '/' . $mainFileName, $cssOut);
        } catch (ParserException $ex) {
            throw new GuidelineAssetBuildFailedException($ex->getMessage());
        }

        $this->removeOldFiles($fullPath, $mainFileName);

        return $fullPath . '/' . $varsFileName;
    }

    /**
     * @param string $path
     */
    private function createDirIfNotExist($path)
    {
        if (!file_exists($path) && !is_dir($path)) {
            mkdir($path);
        }
    }

    /**
     * @param string $path
     * @param string $file
     */
    public function removeOldFiles($path, $file)
    {
        // Delete all scss files
        foreach (glob($path . "/*.scss") as $filename) {
            unlink($filename);
        }

        // Delete old css files
        foreach (glob($path . "/*.css") as $filename) {
            if ($filename !== ($path . '/' . $file)) {
                unlink($filename);
            }
        }
    }
}
