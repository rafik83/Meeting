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

class CampaignView
{
    /** @var int */
    public $id;

    /** @var string */
    public $title;

    /** @var \DateTimeInterface */
    public $createdAt;

    /** @var \DateTimeInterface */
    public $sentAt;

    /** @var \DateTimeInterface */
    public $sent;

    /**
     * @param int                $id
     * @param string             $title
     * @param \DateTimeInterface $createdAt
     */
    public function __construct($id, $title, \DateTimeInterface $createdAt, \DateTimeInterface $sentAt = null)
    {
        $this->id        = $id;
        $this->title     = $title;
        $this->createdAt = $createdAt;
        $this->sentAt    = $sentAt;
        $this->sent      = (bool) $sentAt;
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
            $campaign->getSentAt()
        );
    }
}
