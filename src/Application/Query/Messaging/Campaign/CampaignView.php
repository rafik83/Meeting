<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Messaging\Campaign;

use Proximum\Vimeet\Domain\Model\Messaging\Campaign;
use Proximum\Vimeet\Domain\Model\Messaging\Message;

class CampaignView
{
    /** @var int */
    public $id;

    /** @var string */
    public $title;

    /** @var Message|null */
    public $message;

    /** @var string[] */
    public $recipients;

    /** @var \DateTimeInterface */
    public $createdAt;

    /** @var \DateTimeInterface */
    public $sentAt;

    /** @var bool */
    public $sent;

    /**
     * @param int                     $id
     * @param string                  $title
     * @param Message|null            $message
     * @param \DateTimeInterface      $createdAt
     * @param \DateTimeInterface|null $sentAt
     * @param string[]                $recipients
     */
    public function __construct(
        $id,
        $title,
        \DateTimeInterface $createdAt,
        \DateTimeInterface $sentAt = null,
        Message $message = null,
        $recipients = []
    ) {
        $this->id         = $id;
        $this->title      = $title;
        $this->createdAt  = $createdAt;
        $this->sentAt     = $sentAt;
        $this->message    = $message;
        $this->recipients = $recipients;
        $this->sent       = (bool) $sentAt;
    }

    /**
     * @param Campaign $campaign
     *
     * @return CampaignView
     */
    public static function createFromCampaign(Campaign $campaign)
    {
        return new self(
            $campaign->getId(),
            $campaign->getTitle(),
            $campaign->getCreatedAt(),
            $campaign->getSentAt(),
            $campaign->getMessage(),
            $campaign->getRecipients()
        );
    }
}
