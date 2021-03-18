<?php

namespace Proximum\Vimeet\Tests\Application\ThirdParty\Comexposium\SSO\Application\Query;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\Application\Query\SSORegistrationTypeResolver;
use Proximum\Vimeet\Domain\Event\ExtraParameter\Type as ExtraParameterType;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\Event\ExtraParameterRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;

class SSORegistrationTypeResolverTest extends TestCase
{
    public function testHandle()
    {
        $type = $this->prophesize(Type::class);
        $event = $this->prophesize(Event::class);
        $extraParameter = $this->prophesize(Event\ExtraParameter::class);
        $extraParameter->getValue()->willReturn('1337');

        $extraParameterRepository = $this->prophesize(ExtraParameterRepositoryInterface::class);
        $extraParameterRepository
            ->findByEventAndType(
                $event,
                ExtraParameterType::TYPE_COMEXPOSIUM_VISITOR_TYPE_ID
            )
            ->shouldBeCalled()
            ->willReturn($extraParameter->reveal())
        ;

        $typeRepository = $this->prophesize(TypeRepositoryInterface::class);
        $typeRepository->getById(1337)->shouldBeCalled()->willReturn($type->reveal());

        $SSORegistrationTypeResolver = new SSORegistrationTypeResolver(
            $extraParameterRepository->reveal(),
            $typeRepository->reveal()
        );

        $this->assertEquals($type->reveal(), $SSORegistrationTypeResolver->handle($event->reveal()));
    }
}
