<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Guideline;

use Proximum\Vimeet\Domain\Model\Event;

use Proximum\Vimeet\Application\Components\Slug\Generator as SlugGenerator;

class Generator
{
    /**
     * @var string
     */
    private $assetsPath;

    /**
     * @var SlugGenerator
     */
    private $slugGenerator;

    /**
     * @param string        $assetsPath
     * @param SlugGenerator $slugGenerator
     */
    public function __construct($assetsPath, SlugGenerator $slugGenerator)
    {
        $this->assetsPath    = $assetsPath;
        $this->slugGenerator = $slugGenerator;
    }

    /**
     * @param Event $event
     *
     * @return string
     */
    public function generate(Event $event)
    {
        $gradientLeftColor  = $event->getConfiguration()->getLeftColor();
        $gradientRightColor = $event->getConfiguration()->getRightColor();
        $colorHighlighted   = $event->getConfiguration()->getTextColor();

        $repo = $this->slugGenerator->slugify($event->getTitle());

        $this->createDirIfNotExist($this->assetsPath);

        $fullPath = $this->assetsPath . '/' . $repo;

        $this->createDirIfNotExist($fullPath);

        $content  = implode($this->getBaseAssetVars(), "\n");
        $content .= "\n";
        $content .= sprintf('$gradient-left-color: %s;', $gradientLeftColor) . "\n";
        $content .= sprintf('$gradient-right-color: %s;', $gradientRightColor) . "\n";
        $content .= sprintf('$color-high-lighted: %s;', $colorHighlighted) . "\n";
        $varsFileName = sha1(uniqid()) . '.scss';

        file_put_contents($fullPath . '/' . $varsFileName, $content);

        $this->removeOldFiles($fullPath, $varsFileName);

        return $varsFileName;
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
     * @param string $newFile
     */
    private function removeOldFiles($path, $newFile)
    {
        foreach (glob($path . "/*.scss") as $filename) {
            if ($filename !== ($path . "/" . $newFile)) {
                unlink($filename);
            }
        }
    }

    /**
     * @return array
     */
    private static function getBaseAssetVars()
    {
        return [
            '$bg-body-color: #FFFFFF;',
            '$bg-container-color: #E8E8E9;',
            '$bg-container-darkened-color: #D8D8D8;',
            '$bg-success-color: #67BB1F;',
            '$bg-fail-color: #FF4902;',
            '$gradient-text-color: #FFFFFF;',
            '$color-primary: #2F2F2F;',
            '$color-notification: #FF4902;',
            '$button-text-color: #FFFFFF;',
            '$button-bg-color-inactive: rgba(#FFFFFF, .15);',
            '$button-bg-color-inactive-hover: rgba(#FFFFFF, .3);',
        ];
    }
}
