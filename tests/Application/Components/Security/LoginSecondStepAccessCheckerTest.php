<?php

namespace Proximum\Vimeet\Tests\Application\Components\Security;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Components\Security\LoginSecondStepAccessChecker;
use Proximum\Vimeet\Domain\Event\ExtraParameter\Type;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\Event\ExtraParameterRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;

class LoginSecondStepAccessCheckerTest extends TestCase
{
    /**
     * @dataProvider dataProvider
     */
    public function testAllowedToAccess(bool $emailExists, ?Event\ExtraParameter $extraParameter, bool $expectedResult)
    {
        $event = $this->prophesize(Event::class);

        $userRepository = $this->prophesize(UserRepositoryInterface::class);
        $userRepository->emailExists('whatever@example.net')->shouldBeCalled()->willReturn($emailExists);

        $extraParameterRepository = $this->prophesize(ExtraParameterRepositoryInterface::class);

        if ($emailExists) {
            $extraParameterRepository
                ->findByEventAndType($event, Type::TYPE_COMEXPOSIUM_SSO_ENABLED)
                ->shouldNotBeCalled()
            ;
        } else {
            $extraParameterRepository
                ->findByEventAndType($event, Type::TYPE_COMEXPOSIUM_SSO_ENABLED)
                ->shouldBeCalled()
                ->willReturn($extraParameter)
            ;
        }

        $loginSecondStepAccessChecker = new LoginSecondStepAccessChecker(
            $userRepository->reveal(),
            $extraParameterRepository->reveal()
        );

        $result = $loginSecondStepAccessChecker->allowedToAccess($event->reveal(), 'whatever@example.net');
        $this->assertEquals($expectedResult, $result);
    }

    public function dataProvider()
    {
        $extraParameter = $this->prophesize(Event\ExtraParameter::class);

        return [
            [false, null, false],
            [true, $extraParameter->reveal(), true],
            [true, null, true],
            [false, $extraParameter->reveal(), true]
        ];
    }
}
