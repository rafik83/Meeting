<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Meeting\Message;

class MessageMeetingRequestView
{
    const RIGHT = 'right';
    const LEFT  = 'left';

    /**
     * @var \DateTimeInterface
     */
    public $createdAt;

    /**
     * @var string
     */
    public $sheetName;

    /**
     * @var string
     */
    public $side;

    /**
     * @var string
     */
    public $content;

    /**
     * @var int
     */
    public $sheetId;

    /**
     * @param int                $sheetId
     * @param string             $sheetName
     * @param string             $content
     * @param \DateTimeInterface $createdAt
     * @param string             $side
     */
    public function __construct($sheetId, $sheetName, $content, \DateTimeInterface $createdAt, $side)
    {
        $this->sheetId   = $sheetId;
        $this->sheetName = $sheetName;
        $this->content   = $content;
        $this->createdAt = $createdAt;
        $this->side      = $side;
    }

    /**
     * @return bool
     */
    public function isLeft()
    {
        return $this->side === self::LEFT;
    }
}
