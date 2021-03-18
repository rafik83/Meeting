<?php

namespace Proximum\Vimeet\Tests\Application\ThirdParty\Comexposium\SSO\Application\Query;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\Application\Query\SSOComexposiumViewQuery;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\Application\Query\SSOComexposiumViewQueryHandler;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\Application\View\SSOComexposiumView;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\Converter\LocaleConverter;
use Proximum\Vimeet\Domain\Event\ExtraParameter\Type;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\Event\ExtraParameterRepositoryInterface;

class SSOComexposiumViewQueryHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $extraParameterRepository;

    /** @var ObjectProphecy */
    private $event;

    /** @var string */
    private $comexposiumSSOLoaderLibEndpoint;

    /** @var ObjectProphecy */
    private $localeConverter;

    public function setUp()
    {
        $this->extraParameterRepository = $this->prophesize(ExtraParameterRepositoryInterface::class);
        $this->localeConverter = $this->prophesize(LocaleConverter::class);
        $this->comexposiumSSOLoaderLibEndpoint = 'https://example.net/endpoint';
        $this->event = $this->prophesize(Event::class);
    }

    public function testHandleNotEnabled()
    {
        $this->extraParameterRepository
            ->findByEventAndType($this->event->reveal(), Type::TYPE_COMEXPOSIUM_SSO_ENABLED)
            ->shouldBeCalled()
            ->willReturn(null)
        ;
        $this->localeConverter->formatLocale('fr')->shouldNotBeCalled();

        $handler = new SSOComexposiumViewQueryHandler(
            $this->extraParameterRepository->reveal(),
            $this->localeConverter->reveal(),
            $this->comexposiumSSOLoaderLibEndpoint
        );

        $result = $handler->handle(new SSOComexposiumViewQuery($this->event->reveal(), 'fr'));

        $this->assertEquals(null, $result);
    }

    public function testHandleParametersNotPresent()
    {
        $extraParameter1 = $this->prophesize(Event\ExtraParameter::class);
        $extraParameter2 = $this->prophesize(Event\ExtraParameter::class);
        $extraParameter3 = $this->prophesize(Event\ExtraParameter::class);
        $this->extraParameterRepository
            ->findByEventAndType($this->event->reveal(), Type::TYPE_COMEXPOSIUM_SSO_ENABLED)
            ->shouldBeCalled()
            ->willReturn($extraParameter1->reveal())
        ;

        $this->extraParameterRepository
            ->findByEventAndType($this->event->reveal(), Type::TYPE_COMEXPOSIUM_SSO_APPLICATION)
            ->shouldBeCalled()
            ->willReturn($extraParameter2->reveal())
        ;

        $this->extraParameterRepository
            ->findByEventAndType($this->event->reveal(), Type::TYPE_COMEXPOSIUM_SSO_SALON)
            ->shouldBeCalled()
            ->willReturn($extraParameter3->reveal())
        ;

        $this->extraParameterRepository
            ->findByEventAndType($this->event->reveal(), Type::TYPE_COMEXPOSIUM_SSO_SESSION_SALON)
            ->shouldBeCalled()
            ->willReturn(null)
        ;
        $this->localeConverter->formatLocale('fr')->shouldNotBeCalled();

        $handler = new SSOComexposiumViewQueryHandler(
            $this->extraParameterRepository->reveal(),
            $this->localeConverter->reveal(),
            $this->comexposiumSSOLoaderLibEndpoint
        );

        $result = $handler->handle(new SSOComexposiumViewQuery($this->event->reveal(), 'fr'));

        $this->assertEquals(null, $result);
    }

    public function testHandle()
    {
        $extraParameter1 = $this->prophesize(Event\ExtraParameter::class);
        $extraParameter2 = $this->prophesize(Event\ExtraParameter::class);
        $extraParameter3 = $this->prophesize(Event\ExtraParameter::class);
        $extraParameter4 = $this->prophesize(Event\ExtraParameter::class);
        $this->extraParameterRepository
            ->findByEventAndType($this->event->reveal(), Type::TYPE_COMEXPOSIUM_SSO_ENABLED)
            ->shouldBeCalled()
            ->willReturn($extraParameter1->reveal())
        ;

        $this->extraParameterRepository
            ->findByEventAndType($this->event->reveal(), Type::TYPE_COMEXPOSIUM_SSO_APPLICATION)
            ->shouldBeCalled()
            ->willReturn($extraParameter2->reveal())
        ;

        $this->extraParameterRepository
            ->findByEventAndType($this->event->reveal(), Type::TYPE_COMEXPOSIUM_SSO_SALON)
            ->shouldBeCalled()
            ->willReturn($extraParameter3->reveal())
        ;

        $this->extraParameterRepository
            ->findByEventAndType($this->event->reveal(), Type::TYPE_COMEXPOSIUM_SSO_SESSION_SALON)
            ->shouldBeCalled()
            ->willReturn($extraParameter4->reveal())
        ;

        $this->localeConverter->formatLocale('en')->shouldBeCalled()->willReturn('eng-GB');

        $handler = new SSOComexposiumViewQueryHandler(
            $this->extraParameterRepository->reveal(),
            $this->localeConverter->reveal(),
            $this->comexposiumSSOLoaderLibEndpoint
        );

        $extraParameter2->getValue()->willReturn('application123');
        $extraParameter3->getValue()->willReturn('salon');
        $extraParameter4->getValue()->willReturn('sessionSalon');

        $result = $handler->handle(
            new SSOComexposiumViewQuery($this->event->reveal(), 'en')
        );

        $expected = new SSOComexposiumView(
            'salon',
            'sessionSalon',
            'application123',
            'eng-GB',
            $this->comexposiumSSOLoaderLibEndpoint
        );

        $this->assertEquals($expected, $result);
    }
}
