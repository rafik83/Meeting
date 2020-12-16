<?php

namespace Proximum\Vimeet\Application\Components\Guideline;

use Proximum\Vimeet\Application\Exception\Asset\GuidelineAssetBuildFailedException;
use Proximum\Vimeet\Domain\Model\Event;
use ScssPhp\ScssPhp\Compiler;
use ScssPhp\ScssPhp\Exception\ParserException;
use ScssPhp\ScssPhp\OutputStyle;

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

    public function __construct(
        \Twig_Environment $twig,
        string $rootPath,
        string $webAssetsPath,
        string $bundleGuidelinePath,
        string $fontPath,
        string $imagePath
    ) {
        $this->twig = $twig;
        $this->rootPath = $rootPath;
        $this->webAssetsPath = $webAssetsPath;
        $this->bundleGuidelinePath = $bundleGuidelinePath;
        $this->fontPath = $fontPath;
        $this->imagePath = $imagePath;
    }

    /**
     * @throws GuidelineAssetBuildFailedException
     */
    public function generate(Event $event): string
    {
        $gradientLeftColor = $event->getConfiguration()->getLeftColor();
        $gradientRightColor = $event->getConfiguration()->getRightColor();
        $gradientHeaderLeftColor = $event->getConfiguration()->getHeaderLeftColor();
        $gradientHeaderRightColor = $event->getConfiguration()->getHeaderRightColor();
        $colorHighlighted = $event->getConfiguration()->getTextColor();
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
            'fontPath' => $this->fontPath,
            'imagePath' => $this->imagePath,
            'backgroundImage' => $event->getConfiguration()->getBackgroundImage(),
            'backgroundColor' => $event->getConfiguration()->getBackgroundColor(),
        ]);

        $varsFileName = 'vars-' . sha1(uniqid()) . '.scss';
        $mainFileName = 'main-' . sha1(uniqid()) . '.css';

        $this->put($fullPath . DIRECTORY_SEPARATOR . $varsFileName, $file);

        $scss = new Compiler();

        $scss->setOutputStyle(OutputStyle::COMPRESSED);
        $scss->addImportPath($this->bundleGuidelinePath);

        try {
            $cssOut = $scss->compile(file_get_contents($fullPath . DIRECTORY_SEPARATOR . $varsFileName));
            $this->put($fullPath . DIRECTORY_SEPARATOR . $mainFileName, $cssOut);
        } catch (ParserException $ex) {
            throw new GuidelineAssetBuildFailedException($ex->getMessage());
        }

        $this->removeOldFiles($fullPath, $mainFileName);

        return $this->webAssetsPath . DIRECTORY_SEPARATOR . $repoName . DIRECTORY_SEPARATOR . $mainFileName;
    }

    private function createDirIfNotExist(string $path): void
    {
        if (!file_exists($path) && !is_dir($path)) {
            $this->mkdir($path);
        }
    }

    private function removeOldFiles(string $path, string $file): void
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
     * @throws GuidelineAssetBuildFailedException
     */
    private function unlink(string $filename): void
    {
        if (false === @unlink($filename)) {
            throw new GuidelineAssetBuildFailedException(sprintf('Unable to unlink "%s"', $filename), 0, $this->createLastErrorException());
        }
    }

    /**
     * @throws GuidelineAssetBuildFailedException
     */
    private function mkdir(string $path): void
    {
        if (false === @mkdir($path)) {
            throw new GuidelineAssetBuildFailedException(sprintf('Unable to mkdir "%s"', $path), 0, $this->createLastErrorException());
        }
    }

    /**
     * @throws GuidelineAssetBuildFailedException
     */
    private function put(string $filename, string $contents): void
    {
        if (false === @file_put_contents($filename, $contents)) {
            throw new GuidelineAssetBuildFailedException(sprintf('Unable to put contents in "%s"', $filename), 0, $this->createLastErrorException());
        }
    }

    private function createLastErrorException(): GuidelineAssetBuildFailedException
    {
        return new GuidelineAssetBuildFailedException(print_r(error_get_last(), true));
    }
}
