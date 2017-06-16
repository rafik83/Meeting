<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\OMZ;

use Proximum\Vimeet\Application\Adapter\SerializerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Components\Planning\Formatter\ParticipantPlanningFormatter;
use Proximum\Vimeet\Application\Serializer\Charset;
use Proximum\Vimeet\Application\View\OMZ\OmzUserListView;
use Proximum\Vimeet\Application\View\OMZ\OmzUserView;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Domain\Service\SheetsGroup\GroupNameResolver;
use Proximum\Vimeet\Domain\Service\Type\TypeNameResolver;

class ExportHandler
{
    /** @var UserRepositoryInterface */
    private $userRepository;

    /** @var GroupNameResolver */
    private $groupNameResolver;

    /** @var TypeNameResolver */
    private $typeNameResolver;

    /** @var ParticipantPlanningFormatter */
    private $participantPlanningFormatter;

    /** @var SerializerAdapterInterface */
    private $serializer;

    /** @var TranslatorInterface */
    private $translator;

    /**
     * ExportHandler constructor.
     *
     * @param UserRepositoryInterface      $userRepository
     * @param GroupNameResolver            $groupNameResolver
     * @param TypeNameResolver             $typeNameResolver
     * @param ParticipantPlanningFormatter $participantPlanningFormatter
     * @param SerializerAdapterInterface   $serializer
     * @param TranslatorInterface          $translator
     */
    public function __construct(
        UserRepositoryInterface $userRepository,
        GroupNameResolver $groupNameResolver,
        TypeNameResolver $typeNameResolver,
        ParticipantPlanningFormatter $participantPlanningFormatter,
        SerializerAdapterInterface $serializer,
        TranslatorInterface $translator
    ) {
        $this->userRepository               = $userRepository;
        $this->groupNameResolver            = $groupNameResolver;
        $this->typeNameResolver             = $typeNameResolver;
        $this->participantPlanningFormatter = $participantPlanningFormatter;
        $this->serializer                   = $serializer;
        $this->translator                   = $translator;
    }

    /**
     * @param Export $export
     *
     * @return string normalized datas destinated to the OMZ import
     */
    public function handle(Export $export)
    {
        $usersViews = [];
        $event      = $export->event;

        $this->participantPlanningFormatter->preloadPlanningHandlerForEvent($event);

        foreach ($this->userRepository->findByEvent($event) as $user) {
            $userLocale = $event->getAvailableLocale($user->getLocale());
            $gender     = $user->getGender();

            if (!empty($gender)) {
                $gender = $this->translator->trans(sprintf('gender.%s', $gender));
            }

            $planning = $this->participantPlanningFormatter->formatPlanningFromUserAndEventWithUnallocated(
                $user,
                $event,
                $userLocale
            );

            $usersViews[] = new OmzUserView(
                $user->getId(),
                $this->groupNameResolver->resolve($event, $user),
                null,
                $this->typeNameResolver->resolve($user, $event, $event->getFallback()),
                $gender,
                $user->getFirstName(),
                $user->getLastName(),
                $user->getPosition(),
                null,
                $user->getPhone(),
                $user->getEmail(),
                null,
                $user->getMobile(),
                $planning
            );
        }

       return $this->serializer->serialize(new OmzUserListView($usersViews), 'csv', [
           'charset' => Charset::WINDOWS_1252,
       ]);
    }
}
