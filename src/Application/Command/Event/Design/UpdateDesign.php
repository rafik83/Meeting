<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Event\Design;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Event;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UpdateDesign implements Command
{
    /** @var Event */
    public $event;

    /** @var string */
    public $leftColor;

    /** @var string */
    public $rightColor;

    /** @var string */
    public $headerRightColor;

    /** @var string */
    public $headerLeftColor;

    /** @var string */
    public $textColor;

    /** @var string */
    public $backgroundColor;

    /** @var UploadedFile|null */
    public $backgroundImage;

    /** @var bool */
    public $removeBackgroundImage = false;

    /** @var array */
    public $localizedImages;

    public function __construct(Event $event)
    {
        $this->event = $event;
        $this->leftColor = $event->getConfiguration()->getLeftColor();
        $this->rightColor = $event->getConfiguration()->getRightColor();
        $this->headerLeftColor = $event->getConfiguration()->getHeaderLeftColor();
        $this->headerRightColor = $event->getConfiguration()->getHeaderRightColor();
        $this->textColor = $event->getConfiguration()->getTextColor();
        $this->backgroundColor = $event->getConfiguration()->getBackgroundColor();
        $this->localizedImages = [];

        foreach ($event->getLocales() as $locale) {
            $this->localizedImages[$locale] = [
                'logo' => null,
                'mobileLogo' => null,
            ];
        }
    }

    /**
     * @return bool
     */
    public function isColorsUpdated(): bool
    {
        return $this->leftColor  !== $this->event->getConfiguration()->getLeftColor()
            || $this->rightColor !== $this->event->getConfiguration()->getRightColor()
            || $this->leftColor  !== $this->event->getConfiguration()->getHeaderLeftColor()
            || $this->rightColor !== $this->event->getConfiguration()->getHeaderRightColor()
            || $this->textColor  !== $this->event->getConfiguration()->getTextColor()
            || $this->backgroundColor !== $this->event->getConfiguration()->getBackgroundColor()
        ;
    }

    /**
     * @return bool
     */
    public function backgroundImageChanged(): bool
    {
        return $this->backgroundImage instanceof UploadedFile
            || true === $this->removeBackgroundImage
        ;
    }
}
