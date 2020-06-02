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

    /** @var int */
    public $timeRemainingInSeconds;

    /** @var int */
    public $warningTimeRemainingInSeconds;

    /** @var string|null */
    public $headerImage;

    /** @var WebinarSpeakerView[] */
    public $speakers;

    /** @var int */
    public $currentUserId;

    public function __construct(
        int $currentUserId,
        string $happeningTitle,
        string $token,
        string $sessionId,
        string $apiKey,
        bool $isSpeaker,
        array $speakers,
        TimeRangeView $slot,
        \DateTimeInterface $currentTime,
        int $timeRemainingInSeconds,
        int $warningTimeRemainingInSeconds,
        ?string $headerImage
    ) {
        $this->currentUserId = $currentUserId;
        $this->happeningTitle = $happeningTitle;
        $this->token = $token;
        $this->sessionId = $sessionId;
        $this->apiKey = $apiKey;
        $this->isSpeaker = $isSpeaker;
        $this->slot = $slot;
        $this->currentTime = $currentTime;
        $this->timeRemainingInSeconds = $timeRemainingInSeconds;
        $this->warningTimeRemainingInSeconds = $warningTimeRemainingInSeconds;
        $this->headerImage = $headerImage;
        $this->speakers = $speakers;
    }

    public function getSpeakerInfosByUserId(): string
    {
        $mapping = [];

        foreach ($this->speakers as $speaker) {
            $position = null !== $speaker->position ? '(' . $speaker->position . ')' : '';
            $organization = null !== $speaker->organization ? '- ' . $speaker->organization : '';
            $speakerInfo = sprintf(
                '%s %s %s %s',
                $speaker->firstName,
                $speaker->lastName,
                $position,
                $organization
            );

            $mapping[$speaker->userId] = $speakerInfo;
        }

        return json_encode($mapping);
    }
}
