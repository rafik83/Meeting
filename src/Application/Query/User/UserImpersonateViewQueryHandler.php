<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\User;

use Proximum\Vimeet\Application\Adapter\ImpersonateUrlGeneratorInterface;
use Proximum\Vimeet\Application\View\User\UserImpersonateView;
use Proximum\Vimeet\Application\View\User\UserView;

class UserImpersonateViewQueryHandler
{
    /**
     * @var ImpersonateUrlGeneratorInterface
     */
    private $impersonateUrlGenerator;

    /**
     * UserImpersonateViewQueryHandler constructor.
     *
     * @param ImpersonateUrlGeneratorInterface $impersonateUrlGenerator
     */
    public function __construct(ImpersonateUrlGeneratorInterface $impersonateUrlGenerator)
    {
        $this->impersonateUrlGenerator = $impersonateUrlGenerator;
    }

    /**
     * @param UserImpersonateViewQuery $query
     *
     * @return UserImpersonateView
     */
    public function handle(UserImpersonateViewQuery $query)
    {
        $exitLink = $this->impersonateUrlGenerator->generateExit($query->exitRouteName, [
            'event' => $query->sheet->getEvent()->getId(),
            'sheet' => $query->sheet->getId(),
        ]);

        $parentUserView = new UserView(
            $query->user->getFirstname(),
            $query->user->getLastname(),
            $query->user->getEmail()
        );

        $userView = new UserView(
            $query->user->getFirstName(),
            $query->user->getLastName(),
            $query->user->getLocale()
        );

        return new UserImpersonateView($parentUserView, $userView, $exitLink);
    }
}
