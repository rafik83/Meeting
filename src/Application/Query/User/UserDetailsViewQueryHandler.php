<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\User;

use Proximum\Vimeet\Application\Exception\Sheet\SheetNotFoundException;
use Proximum\Vimeet\Application\View\User\UserDetailsView;
use Proximum\Vimeet\Application\View\User\UserSheetView;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserEventRepositoryInterface;
use Proximum\Vimeet\Domain\UserEvent\Exception\UserEventMissingException;

class UserDetailsViewQueryHandler
{
    /**
     * @var UserEventRepositoryInterface
     */
    private $userEventRepository;

    /**
     * @var SheetRepositoryInterface
     */
    private $sheetRepository;

    /**
     * UserDetailsViewQueryHandler constructor.
     *
     * @param UserEventRepositoryInterface $userEventRepository
     * @param SheetRepositoryInterface     $sheetRepository
     */
    public function __construct(
        UserEventRepositoryInterface $userEventRepository,
        SheetRepositoryInterface $sheetRepository
    ) {
        $this->userEventRepository = $userEventRepository;
        $this->sheetRepository     = $sheetRepository;
    }

    /**
     * @param UserDetailsViewQuery $query
     *
     * @throws SheetNotFoundException
     * @throws UserEventMissingException
     *
     * @return UserDetailsView
     */
    public function handle(UserDetailsViewQuery $query)
    {
        $userSheetListView = [];

        $userEvent = $this->userEventRepository->getUserEvent($query->user, $query->event);

        if (null === $userEvent) {
            throw new UserEventMissingException(
                sprintf(
                    'This user %s is not on this event %s',
                    $query->user->getId(),
                    $query->event->getId()
                )
            );
        }

        $sheets = $this->sheetRepository->getByUser($query->user);

        foreach ($sheets as $sheet) {
            $userSheetListView[] = new UserSheetView($sheet);
        }

        return new UserDetailsView($query->event, $query->user, $userSheetListView);
    }
}
