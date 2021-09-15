<?php

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

    /** @var string */
    public $headerButtonLeftColor;

    /** @var string */
    public $headerButtonRightColor;

    /** @var string */
    public $headerButtonTextColor;

    /** @var UploadedFile|null */
    public $notificationImage;

    public function __construct(Event $event)
    {
        $this->event = $event;
        $this->leftColor = $event->getConfiguration()->getLeftColor();
        $this->rightColor = $event->getConfiguration()->getRightColor();
        $this->headerLeftColor = $event->getConfiguration()->getHeaderLeftColor();
        $this->headerRightColor = $event->getConfiguration()->getHeaderRightColor();
        $this->headerButtonLeftColor = $event->getConfiguration()->getHeaderButtonLeftColor();
        $this->headerButtonRightColor = $event->getConfiguration()->getHeaderButtonRightColor();
        $this->headerButtonTextColor = $event->getConfiguration()->getHeaderButtonTextColor();
        $this->textColor = $event->getConfiguration()->getTextColor();
        $this->backgroundColor = $event->getConfiguration()->getBackgroundColor();
        $this->localizedImages = [];
        $this->notificationImage = [];

        foreach ($event->getLocales() as $locale) {
            $this->localizedImages[$locale] = [
                'logo' => null,
                'mobileLogo' => null,
                'notificationImage' => null
            ];
        }
    }

    /**
     * @return bool
     */
    public function isColorsUpdated(): bool
    {
        return $this->leftColor !== $this->event->getConfiguration()->getLeftColor()
            || $this->rightColor !== $this->event->getConfiguration()->getRightColor()
            || $this->headerLeftColor !== $this->event->getConfiguration()->getHeaderLeftColor()
            || $this->headerRightColor !== $this->event->getConfiguration()->getHeaderRightColor()
            || $this->textColor !== $this->event->getConfiguration()->getTextColor()
            || $this->headerButtonTextColor !== $this->event->getConfiguration()->getHeaderButtonTextColor()
            || $this->headerButtonLeftColor !== $this->event->getConfiguration()->getHeaderButtonLeftColor()
            || $this->headerButtonRightColor !== $this->event->getConfiguration()->getHeaderButtonRightColor()
            || $this->textColor !== $this->event->getConfiguration()->getTextColor()
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
