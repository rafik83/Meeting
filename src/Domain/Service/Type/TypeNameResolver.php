<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Service\Type;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Sheet\GroupRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Service\SheetsGroup\GroupNameResolver;

class TypeNameResolver
{
    /** @var GroupRepositoryInterface */
    private $groupRepository;

    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var GroupNameResolver */
    private $groupNameResolver;

    /**
     * TypeNameResolver constructor.
     *
     * @param GroupRepositoryInterface $groupRepository
     * @param SheetRepositoryInterface $sheetRepository
     * @param GroupNameResolver        $groupNameResolver
     */
    public function __construct(
        GroupRepositoryInterface $groupRepository,
        SheetRepositoryInterface $sheetRepository,
        GroupNameResolver $groupNameResolver
    ) {
        $this->groupRepository  = $groupRepository;
        $this->sheetRepository  = $sheetRepository;
        $this->groupNameResolver = $groupNameResolver;
    }


    /**
     * If a Sheet is not in a Group we return the Type name
     * If a Sheet is in a Group, we return the Type name of the lowest Type position
     *
     * @param Event  $event
     * @param User   $user
     * @param string $locale
     *
     * @return string
     */
    public function resolve(Event $event, User $user, $locale)
    {
        $group      = $this->groupRepository->getByEvent($event);
        $userSheets = $this->sheetRepository->getByUser($user);

        if (null !== $group && $this->sheetRepository->hasSheetWithGroupByUserByEvent($user, $event)) {
            $types = [];

            foreach ($userSheets as $userSheet) {
                $type     = $userSheet->getType();
                $position = $type->getPosition();

                if ($position !== null) {
                    $types[$position] = [$type->getTitle($locale)];
                }
            }

            sort($types);

            return current(reset($types));

        }

        $sheet = $this->sheetRepository->getSheetByEventAndTitle($event, $this->groupNameResolver->resolve($event, $user));

        return $sheet->getType()->getTitle($locale);
    }


}
