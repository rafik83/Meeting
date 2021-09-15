<?php

namespace Proximum\Vimeet\Tests\Application\ThirdParty\LENI\Common\UserExtraData;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\ThirdParty\LENI\Common\UserExtraData\UserExtraDataFingerprintManager;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\User\Event\ExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\User\Event\ExtraData\Type;

class UserExtraDataFingerprintManagerTest extends TestCase
{
    public function testUpdateFingerprint()
    {
        $event = $this->prophesize(Event::class);
        $user = $this->prophesize(User::class);
        $dateTime = new \DateTime();

        $extraData = new User\Event\ExtraData(
            $user->reveal(),
            $event->reveal(),
            Type::LENI_FINGERPRINT,
            'whatever-data',
            $dateTime
        );

        $extraDataRepository = $this->prophesize(ExtraDataRepositoryInterface::class);
        $extraDataRepository
            ->getExtraDataForEventNameAndUser($event, Type::LENI_FINGERPRINT, $user)
            ->shouldBeCalled()
            ->willReturn($extraData)
        ;

        $extraDataRepository->add($extraData)->shouldNotBeCalled();
        $extraDataRepository->set($extraData)->shouldBeCalled();

        $userExtraDataFingerprintManager = new UserExtraDataFingerprintManager(
            $extraDataRepository->reveal(),
            $dateTime
        );
        $userExtraDataFingerprintManager->addOrUpdateFingerprint($event->reveal(), $user->reveal(), 'whatever-data');
    }

    public function testAddFingerprint()
    {
        $event = $this->prophesize(Event::class);
        $user = $this->prophesize(User::class);
        $dateTime = new \DateTime();

        $extraData = new User\Event\ExtraData(
            $user->reveal(),
            $event->reveal(),
            Type::LENI_FINGERPRINT,
            'whatever-data',
            $dateTime
        );

        $extraDataRepository = $this->prophesize(ExtraDataRepositoryInterface::class);
        $extraDataRepository
            ->getExtraDataForEventNameAndUser($event, Type::LENI_FINGERPRINT, $user)
            ->shouldBeCalled()
            ->willReturn(null)
        ;

        $extraDataRepository->set($extraData)->shouldNotBeCalled();
        $extraDataRepository->add($extraData)->shouldBeCalled();

        $userExtraDataFingerprintManager = new UserExtraDataFingerprintManager(
            $extraDataRepository->reveal(),
            $dateTime
        );
        $userExtraDataFingerprintManager->addOrUpdateFingerprint($event->reveal(), $user->reveal(), 'whatever-data');
    }
}
