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
use Proximum\Vimeet\Application\Exception\Asset\GuidelineAssetBuildFailedException;
use Proximum\Vimeet\Domain\Model\Event;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class Generator
{
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
     * @param \Twig_Environment $twig
     * @param string            $webAssetsPath
     * @param string            $bundleGuidelinePath
     */
    public function __construct(
        \Twig_Environment $twig,
        $webAssetsPath,
        $bundleGuidelinePath
    ) {
        $this->webAssetsPath       = $webAssetsPath;
        $this->twig                = $twig;
        $this->bundleGuidelinePath = $bundleGuidelinePath;
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
            'gradientLeftColor'  => $gradientLeftColor,
            'gradientRightColor' => $gradientRightColor,
            'colorHighLighted'   => $colorHighlighted,
            'guideline_path'     => $this->bundleGuidelinePath,
        ]);

        $varsFileName = 'vars-' . sha1(uniqid()) . '.scss';
        $mainFileName = 'main-' . sha1(uniqid()) . '.css';

        file_put_contents($fullPath . '/' . $varsFileName, $file);

        $process = new Process(sprintf('gulp event-sass --srcFile=%s --destination=%s --buildFile=%s', $varsFileName, $fullPath, $mainFileName));

        try {
            $process->mustRun();
        } catch (ProcessFailedException $e) {
            throw new GuidelineAssetBuildFailedException(
                sprintf('Error during the gulp event-sass with the message: %s', $e->getMessage())
            );
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
     * @param string $newFile
     */
    private function removeOldFiles($path, $newFile)
    {
        // Delete all scss files
        foreach (glob($path . "/*.scss") as $filename) {
            unlink($filename);
        }

        // Delete old css files
        foreach (glob($path . "/*.css") as $filename) {
            if ($filename !== ($path . "/" . $newFile)) {
                unlink($filename);
            }
        }
    }
}
