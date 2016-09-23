<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\User;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserEventRepositoryInterface;

class TypeResolver
{
    /**
     * @var UserEventRepositoryInterface
     */
    private $userEventRepository;

    /**
     * @var TypeRepositoryInterface
     */
    private $typeRepository;

    /**
     * TypeResolver constructor.
     *
     * @param UserEventRepositoryInterface $userEventRepository
     * @param TypeRepositoryInterface      $typeRepository
     */
    public function __construct(
        UserEventRepositoryInterface $userEventRepository,
        TypeRepositoryInterface $typeRepository
    ) {
        $this->userEventRepository = $userEventRepository;
        $this->typeRepository      = $typeRepository;
    }

    /**
     * @param User  $user
     * @param Event $event
     * @param Type  $type
     */
    public function resolve(User $user, Event $event, Type $type)
    {
        $userEvent = $this->userEventRepository->getUserEvent($user, $event);

        if ($userEvent->getType() !== $type) {
            $userEvent->setType($type);
            $this->userEventRepository->set($userEvent);
        }
    }
}
