<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Happening;

use Proximum\Vimeet\Domain\Time\TimeRangeView;

class WebinarView
{
    /** @var string */
    public $happeningTitle;

    /** @var string */
    public $token;

    /** @var string */
    public $sessionId;

    /** @var string */
    public $apiKey;

    /** @var bool */
    public $isSpeaker;

    /** @var TimeRangeView */
    public $slot;

    /** @var \DateTimeInterface */
    public $currentTime;

    /** @var string|null */
    public $headerImage;

    public function __construct(
        string $happeningTitle,
        string $token,
        string $sessionId,
        string $apiKey,
        bool $isSpeaker,
        TimeRangeView $slot,
        \DateTimeInterface $currentTime,
        ?string $headerImage
    ) {
        $this->happeningTitle = $happeningTitle;
        $this->token = $token;
        $this->sessionId = $sessionId;
        $this->apiKey = $apiKey;
        $this->isSpeaker = $isSpeaker;
        $this->slot = $slot;
        $this->currentTime = $currentTime;
        $this->headerImage = $headerImage;
    }
}
