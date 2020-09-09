<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Tip\Event;

class TipTranslationView
{
    /** @var int */
    public $id;

    /** @var string|null */
    public $title;

    /** @var string|null */
    public $content;

    /** @var string */
    public $adminTitle;

    /** @var string */
    public $display;

    /** @var null|bool */
    public $conditionHasCart;

    /** @var null|bool */
    public $conditionHasRemainingToPay;

    /** @var null|bool */
    public $conditionIsPhoneConfirmed;

    /** @var null|bool */
    public $conditionIsCompleteSheet;

    /** @var null|bool */
    public $conditionHasPendingMeetingProposition;

    /** @var null|array */
    public $conditionOnOrders;

    /** @var bool */
    public $isOpened = false;

    /** @var string */
    public $image = null;

    public function __construct(
        int $id,
        ?string $title = null,
        ?string $content = null,
        string $adminTitle,
        string $display,
        ?bool $conditionHasCart = null,
        ?bool $conditionHasRemainingToPay = null,
        ?bool $conditionIsPhoneConfirmed = null,
        ?bool $conditionIsCompleteSheet = null,
        ?bool $conditionHasPendingMeetingProposition = null,
        ?array $conditionOnOrders = null,
        ?string $image = null
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->content = $content;
        $this->adminTitle = $adminTitle;
        $this->display = $display;
        $this->conditionHasCart = $conditionHasCart;
        $this->conditionHasRemainingToPay = $conditionHasRemainingToPay;
        $this->conditionIsPhoneConfirmed = $conditionIsPhoneConfirmed;
        $this->conditionIsCompleteSheet = $conditionIsCompleteSheet;
        $this->conditionHasPendingMeetingProposition = $conditionHasPendingMeetingProposition;
        $this->conditionOnOrders = $conditionOnOrders;
        $this->image = $image;
    }

    public function getImage()
    {
        return $this->image;
    }

    public function setImage($image)
    {
        $this->image = $image;
    }
}
