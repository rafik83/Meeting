<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\Converter;

use Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\UserInformationGetter;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\View\UserInformationView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Domain\User\Event\ExtraData\Type;
use Proximum\Vimeet\Infrastructure\Repository\User\Event\ExtraDataRepository;

class EmailToUserConverter
{
    /** @var UserInformationGetter */
    private $userInformationGetter;

    /** @var UserRepositoryInterface */
    private $userRepository;

    /** @var ExtraDataRepository */
    private $userEventExtraDataRepository;

    /** @var \DateTimeInterface */
    private $dateTime;

    public function __construct(
        UserInformationGetter $userInformationGetter,
        UserRepositoryInterface $userRepository,
        ExtraDataRepository $userEventExtraDataRepository,
        \DateTimeInterface $dateTime
    ) {
        $this->userInformationGetter = $userInformationGetter;
        $this->userRepository = $userRepository;
        $this->userEventExtraDataRepository = $userEventExtraDataRepository;
        $this->dateTime = $dateTime;
    }

    public function handle(Event $event, string $email, string $locale): ?User
    {
        $userInformationView = $this->userInformationGetter->handle($event, $email, $locale);

        if ($userInformationView instanceof UserInformationView) {
            return $this->createUser($event, $userInformationView);
        }

        return null;
    }

    private function createUser(Event $event, UserInformationView $userInformationView): User
    {
        $user = new User($userInformationView->email, '', '', $userInformationView->locale);
        $user->welcome();

        $account = new User\Account();
        $account->setGender($userInformationView->gender);
        $account->setFirstName($userInformationView->firstname);
        $account->setLastName($userInformationView->lastname);
        $account->setPhone($userInformationView->mobilePhone);
        $account->setMobile($userInformationView->mobilePhone);
        $account->setCompanyCountry($userInformationView->country);
        $account->setCountry($userInformationView->country);
        $user->setAccount($account);

        $this->userRepository->add($user);

        $this->userEventExtraDataRepository->add(
            new User\Event\ExtraData(
                $user, $event, Type::IMPORTED_FROM_COMEXPOSIUM, null, $this->dateTime
            )
        );

        return $user;
    }
}
