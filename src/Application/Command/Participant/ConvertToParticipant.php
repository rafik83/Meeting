<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Participant;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Type;

class ConvertToParticipant
{
    /** @var Event */
    public $event;

    /** @var Type */
    public $type;

    /** @var string */
    public $email;

    /** @var string */
    public $locale;

    /** @var array */
    public $dataIndexedByTag;

    /** @var null|string */
    public $userEventExtraDataType;

    public function __construct(
        Event $event,
        Type $type,
        string $email,
        string $locale,
        array $dataIndexedByTag,
        ?string $userEventExtraDataType = null
    ) {
        $this->event = $event;
        $this->type = $type;
        $this->email = $email;
        $this->locale = $locale;
        $this->dataIndexedByTag = $dataIndexedByTag;
        $this->userEventExtraDataType = $userEventExtraDataType;
    }
}
