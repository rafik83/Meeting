<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Type\Badge;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Badge;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Type;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Configure implements Command
{
    /** @var Event */
    public $event;

    /** @var Type */
    public $type;

    /** @var null|Badge */
    public $badge;

    /** @var UploadedFile */
    public $header;

    /** @var bool */
    public $showHeader;

    /**
     * @var string
     *
     * @see Badge::FOOTER_SHOW_POSSIBILITIES
     */
    public $showFooterTypeOrCategory;

    /** @var string */
    public $footerTextColor;

    /** @var string */
    public $footerColor;

    /** @var bool */
    public $showPosition;

    /** @var bool */
    public $showFirstName;

    /** @var bool */
    public $showLastName;

    /** @var bool */
    public $showSheetTitle;

    /** @var bool */
    public $showQRCode;

    /** @var bool */
    public $activated;

    /** @var bool */
    public $conditioned;

    /** @var bool */
    public $conditionedByPackage;

    /** @var array */
    public $conditionedByStates;

    /** @var bool */
    public $showCountry;

    public function __construct(Event $event, Type $type, ?Badge $badge = null)
    {
        $this->event = $event;
        $this->type = $type;
        $this->badge = $badge;

        $this->showHeader = true;
        $this->showFooterTypeOrCategory = Badge::FOOTER_SHOW_TYPE;
        $this->footerTextColor = '#ffffff';
        $this->footerColor = '#000000';
        $this->showPosition = true;
        $this->showFirstName = true;
        $this->showLastName = true;
        $this->showSheetTitle = true;
        $this->showQRCode = true;
        $this->activated = true;
        $this->conditioned = false;
        $this->conditionedByPackage = false;
        $this->conditionedByStates = [];
        $this->showCountry = false;

        if ($badge instanceof Badge) {
            $this->showHeader = $badge->isShowHeader();
            $this->showFooterTypeOrCategory = $badge->getShowFooterTypeOrCategory();
            $this->footerTextColor = $badge->getFooterTextColor();
            $this->footerColor = $badge->getFooterColor();
            $this->showPosition = $badge->isShowPosition();
            $this->showFirstName = $badge->isShowFirstName();
            $this->showLastName = $badge->isShowLastName();
            $this->showSheetTitle = $badge->isShowSheetTitle();
            $this->showQRCode = $badge->isShowQRCode();
            $this->activated = $badge->isActivated();
            $this->conditioned = $badge->isConditioned();
            $this->conditionedByPackage = $badge->isConditionedByPackage();
            $this->conditionedByStates = $badge->getConditionedByStates();
            $this->showCountry = $badge->isShowCountry();
        }
    }
}
