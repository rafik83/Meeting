<?php

namespace Proximum\Vimeet\Tests\Infrastructure\Encryption;

use Proximum\Vimeet\Application\Adapter\ProtectedKeyInterface;
use Proximum\Vimeet\Application\Adapter\UserEventProtectedKeyPasswordGetterInterface;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\User\Event\ExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\User\Event\ExtraData\Type;
use Proximum\Vimeet\Infrastructure\Encryption\UserEventProtectedKeyGetterAdapter;
use PHPUnit\Framework\TestCase;

class UserEventProtectedKeyGetterAdapterTest extends TestCase
{
    private $event;
    private $user;
    private $userEventExtraDataRepository;
    private $protectedKey;
    private $userEventProtectedKeyPasswordGetter;
    private $dateTime;
    private $userEventProtectedKeyGetterAdapter;

    public function setUp()
    {
        $this->event = $this->prophesize(Event::class);
        $this->user = $this->prophesize(User::class);

        $this->userEventExtraDataRepository = $this->prophesize(ExtraDataRepositoryInterface::class);
        $this->protectedKey = $this->prophesize(ProtectedKeyInterface::class);
        $this->userEventProtectedKeyPasswordGetter = $this->prophesize(
            UserEventProtectedKeyPasswordGetterInterface::class
        );
        $this->dateTime = new \DateTime();

        $this->userEventProtectedKeyGetterAdapter = new UserEventProtectedKeyGetterAdapter(
            $this->userEventExtraDataRepository->reveal(),
            $this->protectedKey->reveal(),
            $this->userEventProtectedKeyPasswordGetter->reveal(),
            $this->dateTime
        );
    }

    public function testGetProtectedKeyStoredInExtraData()
    {
        $extraData = $this->prophesize(User\Event\ExtraData::class);
        $extraData->getValue()->willReturn('myUserKey');

        $this
            ->userEventExtraDataRepository
            ->getExtraDataForEventNameAndUser(
                $this->event->reveal(),
                Type::PROTECTED_KEY,
                $this->user->reveal()
            )
            ->shouldBeCalled()
            ->willReturn($extraData->reveal())
        ;

        $result = $this->userEventProtectedKeyGetterAdapter->getProtectedKeyByEventAndUser(
            $this->event->reveal(),
            $this->user->reveal()
        );

        $this->assertEquals('myUserKey', $result);
    }

    public function testGenerateAndStoreNewProtectedKey()
    {
        $this
            ->userEventExtraDataRepository
            ->getExtraDataForEventNameAndUser(
                $this->event->reveal(),
                Type::PROTECTED_KEY,
                $this->user->reveal()
            )
            ->shouldBeCalled()
            ->willReturn(null)
        ;

        $this
            ->userEventProtectedKeyPasswordGetter
            ->getProtectedKeyPasswordByEventAndUser(
                $this->event->reveal(),
                $this->user->reveal()
            )
            ->shouldBeCalled()
            ->willReturn('_my_generated_password_')
        ;

        $this
            ->protectedKey
            ->getKeyProtectedByPassword('_my_generated_password_')
            ->shouldBeCalled()
            ->willReturn('__very_secure_protected-KEY')
        ;

        $this
            ->userEventExtraDataRepository
            ->add(
                new User\Event\ExtraData(
                    $this->user->reveal(),
                    $this->event->reveal(),
                    Type::PROTECTED_KEY,
                    '__very_secure_protected-KEY',
                    $this->dateTime
                )

            )
            ->shouldBeCalled()
        ;

        $result = $this->userEventProtectedKeyGetterAdapter->getProtectedKeyByEventAndUser(
            $this->event->reveal(),
            $this->user->reveal()
        );

        $this->assertEquals('__very_secure_protected-KEY', $result);
    }
}
