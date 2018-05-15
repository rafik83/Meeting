<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Participant\Upload;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Template\TemplateObject\UploadableObjectInterface;

class UploadFile
{
    /** @var Event */
    private $event;

    /** @var User */
    private $user;

    /** @var UploadableObjectInterface */
    private $object;

    /** @var array */
    private $data;

    public function __construct(
        Event $event,
        User $user,
        UploadableObjectInterface $object,
        array $data
    ) {
        $this->object = $object;
        $this->data = $data;
        $this->event = $event;
        $this->user = $user;
    }

    public function getEvent(): Event
    {
        return $this->event;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getObject(): UploadableObjectInterface
    {
        return $this->object;
    }

    public function getData(): array
    {
        return $this->data;
    }
}
