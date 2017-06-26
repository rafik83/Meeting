<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\User\Agenda\Version;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;

class Generator
{
    /** @var RequestRepositoryInterface */
    private $requestRepository;

    /** @var VersionNormalizer */
    private $versionNormalizer;

    /**
     * @param RequestRepositoryInterface $requestRepository
     * @param VersionNormalizer          $versionNormalizer
     */
    public function __construct(RequestRepositoryInterface $requestRepository, VersionNormalizer $versionNormalizer)
    {
        $this->requestRepository = $requestRepository;
        $this->versionNormalizer = $versionNormalizer;
    }

    /**
     * @param Event $event
     * @param User  $user
     *
     * @return array
     */
    public function generate(Event $event, User $user): array
    {
        $requests = $this->requestRepository->getRequestsPlacedByEventAndUser($event, $user);

        return $this->versionNormalizer->normalize($requests);
    }
}
