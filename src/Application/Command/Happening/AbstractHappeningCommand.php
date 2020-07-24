<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Happening;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Happening\Category;
use Proximum\Vimeet\Domain\Model\Type;

class AbstractHappeningCommand implements Command
{
    public const TYPE_DEFAULT = 'default';
    public const TYPE_WEBINAR = 'webinar';
    public const TYPE_WEBINAR_INTERACTIVE = 'webinar_interactive';
    public const TYPE_WEBINAR_VIDEO = 'webinar_video';

    /** @var Category */
    public $category;

    /** @var \DateTimeInterface */
    public $begin;

    /** @var \DateTimeInterface */
    public $end;

    /** @var Type[] */
    public $types = [];

    /** @var array */
    public $translations = [];

    /** @var array */
    public $talkings = [];

    /** @var bool */
    public $questionAllowed;

    /** @var int|null */
    public $limitParticipant;

    /** @var null|string */
    public $invitationCode;

    /** @var bool */
    public $webinar;

    /** @var string|null $liveUrl url for iframe live (webinar only) */
    public $liveUrl;

    /** @var string */
    public $happeningType = self::TYPE_DEFAULT;

    /** @var bool */
    public $sidebarAllowed;

    public function isWebinar(): bool
    {
        return self::TYPE_WEBINAR === $this->happeningType || self::TYPE_WEBINAR_INTERACTIVE === $this->happeningType;
    }

    public function isInteractiveWebinar(): bool
    {
        return self::TYPE_WEBINAR_INTERACTIVE === $this->happeningType;
    }

    public function isVideoWebinar(): bool
    {
        return self::TYPE_WEBINAR_VIDEO === $this->happeningType;
    }
}
