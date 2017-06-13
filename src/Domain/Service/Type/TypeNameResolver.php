<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Service\Type;

use InvalidArgumentException;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;

class TypeNameResolver
{
    /** @var TypeRepositoryInterface */
    private $typeRepository;

    /**
     * TypeNameResolver constructor.
     *
     * @param TypeRepositoryInterface $typeRepository
     */
    public function __construct(TypeRepositoryInterface $typeRepository)
    {
        $this->typeRepository = $typeRepository;
    }

    /**
     * @param User   $user
     * @param Event  $event
     * @param string $locale
     *
     * @return string
     */
    public function resolve(User $user, Event $event, $locale)
    {
        $type = $this->typeRepository->getFirstPositionTypeByEventAndUser($event, $user);

        if ($type === null) {
            throw new InvalidArgumentException('Type cannot be null');
        }

        return $type->getTitle($locale);
    }
}
