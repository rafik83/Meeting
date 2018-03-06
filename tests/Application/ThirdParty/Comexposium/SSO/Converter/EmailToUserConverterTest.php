<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\ThirdParty\Comexposium\SSO\Converter;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\Converter\EmailToUserConverter;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\UserInformationGetter;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\View\UserInformationView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Domain\User\Event\ExtraData\Type;
use Proximum\Vimeet\Infrastructure\Repository\User\Event\ExtraDataRepository;

class EmailToUserConverterTest extends TestCase
{
    /** @var ObjectProphecy */
    private $event;

    /** @var ObjectProphecy */
    private $userInformationGetter;

    /** @var ObjectProphecy */
    private $userRepository;

    /** @var ObjectProphecy */
    private $userEventExtraDataRepository;

    /** @var EmailToUserConverter */
    private $emailToUserConverter;

    /** @var \DateTimeInterface */
    private $dateTime;

    /** @var string */
    private $email;

    /** @var string */
    private $locale;

    public function setUp()
    {
        $this->email = 'bruce.willis@die.hard';
        $this->locale = 'en';
        $this->event = $this->prophesize(Event::class);

        $this->userInformationGetter = $this->prophesize(UserInformationGetter::class);
        $this->userRepository = $this->prophesize(UserRepositoryInterface::class);
        $this->userEventExtraDataRepository = $this->prophesize(ExtraDataRepository::class);

        $this->dateTime = new \DateTime();

        $this->emailToUserConverter = new EmailToUserConverter(
            $this->userInformationGetter->reveal(),
            $this->userRepository->reveal(),
            $this->userEventExtraDataRepository->reveal(),
            $this->dateTime
        );
    }

    public function testHandleNull()
    {
        $this
            ->userInformationGetter
            ->handle($this->event->reveal(), $this->email, $this->locale)
            ->shouldBeCalled()
            ->willReturn(null)
        ;

        $this->assertNull($this->emailToUserConverter->handle($this->event->reveal(), $this->email, $this->locale));
    }

    public function testHandle()
    {
        $expectedUserInformationView = new UserInformationView(
            $this->email,
            'man',
            'Bruce',
            'Willis',
            '+33699887766',
            'FR',
            $this->locale
        );

        $this
            ->userInformationGetter
            ->handle($this->event->reveal(), $this->email, $this->locale)
            ->shouldBeCalled()
            ->willReturn($expectedUserInformationView)
        ;

        $expectedUser = new User($this->email, '', '', $this->locale);
        $expectedUser->welcome();

        $account = new User\Account();
        $account->setGender('man');
        $account->setFirstName('Bruce');
        $account->setLastName('Willis');
        $account->setPhone('+33699887766');
        $account->setMobile('+33699887766');
        $account->setCompanyCountry('FR');
        $account->setCountry('FR');
        $expectedUser->setAccount($account);

        $this
            ->userRepository
            ->add($expectedUser)
            ->shouldBeCalled()
        ;

        $this
            ->userEventExtraDataRepository
            ->add(
                new User\Event\ExtraData(
                    $expectedUser, $this->event->reveal(), Type::IMPORTED_FROM_COMEXPOSIUM, null, $this->dateTime
                )
            )
            ->shouldBeCalled()
        ;

        $this->assertEquals(
            $expectedUser,
            $this->emailToUserConverter->handle($this->event->reveal(), $this->email, $this->locale)
        );
    }
}
