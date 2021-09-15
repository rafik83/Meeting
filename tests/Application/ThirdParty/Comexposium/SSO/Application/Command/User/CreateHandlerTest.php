<?php

namespace Proximum\Vimeet\Tests\Application\ThirdParty\Comexposium\SSO\Application\Command\User;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\HttpAdapterInterface;
use Proximum\Vimeet\Application\Exception\Adapter\Http\ServerErrorException;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\Application\Command\User\Create;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\Application\Command\User\CreateHandler;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\Converter\LocaleConverter;
use Proximum\Vimeet\Application\View\Adapter\Http\Response;
use Proximum\Vimeet\Domain\Event\ExtraParameter\Type;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\Event\ExtraParameterRepositoryInterface;

class CreateHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $httpAdapter;

    /** @var ObjectProphecy */
    private $extraParameterRepository;

    /** @var ObjectProphecy */
    private $localeConverter;

    /** @var ObjectProphecy */
    private $comexposiumSsoCreateUserEndPoint;

    /** @var ObjectProphecy */
    private $event;

    public function setUp()
    {
        $this->event = $this->prophesize(Event::class);
        $this->httpAdapter = $this->prophesize(HttpAdapterInterface::class);
        $this->extraParameterRepository = $this->prophesize(ExtraParameterRepositoryInterface::class);
        $this->localeConverter = $this->prophesize(LocaleConverter::class);
        $this->comexposiumSsoCreateUserEndPoint = 'https://compexosium-sso.endpoint/create/user';
    }

    public function testHandleWithoutParameters()
    {
        $extraParameterSalon = $this->prophesize(Event\ExtraParameter::class);
        $extraParameterSessionSalon = $this->prophesize(Event\ExtraParameter::class);
        $this->extraParameterRepository
            ->findByEventAndType($this->event->reveal(), Type::TYPE_COMEXPOSIUM_SSO_APPLICATION)
            ->shouldBeCalled()
            ->willReturn(null)
        ;
        $this->extraParameterRepository
            ->findByEventAndType($this->event->reveal(), Type::TYPE_COMEXPOSIUM_SSO_SALON)
            ->shouldBeCalled()
            ->willReturn($extraParameterSalon->reveal())
        ;
        $this->extraParameterRepository
            ->findByEventAndType($this->event->reveal(), Type::TYPE_COMEXPOSIUM_SSO_SESSION_SALON)
            ->shouldBeCalled()
            ->willReturn($extraParameterSessionSalon->reveal())
        ;

        $this->httpAdapter->post(Argument::any())->shouldNotBeCalled();
        $this->localeConverter->formatLocale(Argument::any())->shouldNotBeCalled();

        $handler = new CreateHandler(
            $this->httpAdapter->reveal(),
            $this->extraParameterRepository->reveal(),
            $this->comexposiumSsoCreateUserEndPoint,
            $this->localeConverter->reveal()
        );

        $result = $handler->handle(new Create($this->event->reveal(), 'email@example.net', 'fr'));

        $this->assertEquals(CreateHandler::RESPONSE_MISSING_PARAMETERS, $result);
    }

    public function testHandle()
    {
        $extraParameterSalon = $this->prophesize(Event\ExtraParameter::class);
        $extraParameterSessionSalon = $this->prophesize(Event\ExtraParameter::class);
        $extraParameterApplication = $this->prophesize(Event\ExtraParameter::class);

        $extraParameterSalon->getValue()->willReturn('salon');
        $extraParameterSessionSalon->getValue()->willReturn('sessionSalon');
        $extraParameterApplication->getValue()->willReturn('application123');

        $this->extraParameterRepository
            ->findByEventAndType($this->event->reveal(), Type::TYPE_COMEXPOSIUM_SSO_APPLICATION)
            ->shouldBeCalled()
            ->willReturn($extraParameterApplication)
        ;
        $this->extraParameterRepository
            ->findByEventAndType($this->event->reveal(), Type::TYPE_COMEXPOSIUM_SSO_SALON)
            ->shouldBeCalled()
            ->willReturn($extraParameterSalon->reveal())
        ;
        $this->extraParameterRepository
            ->findByEventAndType($this->event->reveal(), Type::TYPE_COMEXPOSIUM_SSO_SESSION_SALON)
            ->shouldBeCalled()
            ->willReturn($extraParameterSessionSalon->reveal())
        ;

        $payload = [
            'email' => 'email@example.net',
            'fromSalon' => 'salon',
            'fromSessionSalon' => 'sessionSalon',
            'language' => 'fre-FR',
            'fromThirdParty' => 'application123',
        ];

        $response = new Response(200, json_encode([
            'result' => [
                'statusCode' => 0
            ]
        ]));

        $this->httpAdapter->post(
            $this->comexposiumSsoCreateUserEndPoint,
            [],
            json_encode($payload)
        )->shouldBeCalled()
        ->willReturn($response);
        $this->localeConverter->formatLocale('fr')->shouldBeCalled()->willReturn('fre-FR');

        $handler = new CreateHandler(
            $this->httpAdapter->reveal(),
            $this->extraParameterRepository->reveal(),
            $this->comexposiumSsoCreateUserEndPoint,
            $this->localeConverter->reveal()
        );

        $result = $handler->handle(new Create($this->event->reveal(), 'email@example.net', 'fr'));

        $this->assertEquals(CreateHandler::RESPONSE_CREATED, $result);
    }

    public function testHandleAlreadyCreated()
    {
        $extraParameterSalon = $this->prophesize(Event\ExtraParameter::class);
        $extraParameterSessionSalon = $this->prophesize(Event\ExtraParameter::class);
        $extraParameterApplication = $this->prophesize(Event\ExtraParameter::class);

        $extraParameterSalon->getValue()->willReturn('salon');
        $extraParameterSessionSalon->getValue()->willReturn('sessionSalon');
        $extraParameterApplication->getValue()->willReturn('application123');

        $this->extraParameterRepository
            ->findByEventAndType($this->event->reveal(), Type::TYPE_COMEXPOSIUM_SSO_APPLICATION)
            ->shouldBeCalled()
            ->willReturn($extraParameterApplication)
        ;
        $this->extraParameterRepository
            ->findByEventAndType($this->event->reveal(), Type::TYPE_COMEXPOSIUM_SSO_SALON)
            ->shouldBeCalled()
            ->willReturn($extraParameterSalon->reveal())
        ;
        $this->extraParameterRepository
            ->findByEventAndType($this->event->reveal(), Type::TYPE_COMEXPOSIUM_SSO_SESSION_SALON)
            ->shouldBeCalled()
            ->willReturn($extraParameterSessionSalon->reveal())
        ;

        $payload = [
            'email' => 'email@example.net',
            'fromSalon' => 'salon',
            'fromSessionSalon' => 'sessionSalon',
            'language' => 'fre-FR',
            'fromThirdParty' => 'application123',
        ];

        $response = new Response(200, json_encode([
            'result' => [
                'statusCode' => 143
            ]
        ]));

        $this->httpAdapter
            ->post(
                $this->comexposiumSsoCreateUserEndPoint,
                [],
                json_encode($payload)
            )
            ->shouldBeCalled()
            ->willReturn($response)
        ;
        $this->localeConverter->formatLocale('fr')->shouldBeCalled()->willReturn('fre-FR');

        $handler = new CreateHandler(
            $this->httpAdapter->reveal(),
            $this->extraParameterRepository->reveal(),
            $this->comexposiumSsoCreateUserEndPoint,
            $this->localeConverter->reveal()
        );

        $result = $handler->handle(new Create($this->event->reveal(), 'email@example.net', 'fr'));

        $this->assertEquals(CreateHandler::RESPONSE_ALREADY_CREATED, $result);
    }

    public function testHandleError()
    {
        $extraParameterSalon = $this->prophesize(Event\ExtraParameter::class);
        $extraParameterSessionSalon = $this->prophesize(Event\ExtraParameter::class);
        $extraParameterApplication = $this->prophesize(Event\ExtraParameter::class);

        $extraParameterSalon->getValue()->willReturn('salon');
        $extraParameterSessionSalon->getValue()->willReturn('sessionSalon');
        $extraParameterApplication->getValue()->willReturn('application123');

        $this->extraParameterRepository
            ->findByEventAndType($this->event->reveal(), Type::TYPE_COMEXPOSIUM_SSO_APPLICATION)
            ->shouldBeCalled()
            ->willReturn($extraParameterApplication)
        ;
        $this->extraParameterRepository
            ->findByEventAndType($this->event->reveal(), Type::TYPE_COMEXPOSIUM_SSO_SALON)
            ->shouldBeCalled()
            ->willReturn($extraParameterSalon->reveal())
        ;
        $this->extraParameterRepository
            ->findByEventAndType($this->event->reveal(), Type::TYPE_COMEXPOSIUM_SSO_SESSION_SALON)
            ->shouldBeCalled()
            ->willReturn($extraParameterSessionSalon->reveal())
        ;

        $payload = [
            'email' => 'email@example.net',
            'fromSalon' => 'salon',
            'fromSessionSalon' => 'sessionSalon',
            'language' => 'fre-FR',
            'fromThirdParty' => 'application123',
        ];

        $response = new Response(200, json_encode([
            'result' => [
                'statusCode' => 123
            ]
        ]));

        $this->httpAdapter
            ->post(
                $this->comexposiumSsoCreateUserEndPoint,
                [],
                json_encode($payload)
            )
            ->shouldBeCalled()
            ->willReturn($response)
        ;
        $this->localeConverter->formatLocale('fr')->shouldBeCalled()->willReturn('fre-FR');

        $handler = new CreateHandler(
            $this->httpAdapter->reveal(),
            $this->extraParameterRepository->reveal(),
            $this->comexposiumSsoCreateUserEndPoint,
            $this->localeConverter->reveal()
        );

        $result = $handler->handle(new Create($this->event->reveal(), 'email@example.net', 'fr'));

        $this->assertEquals(CreateHandler::RESPONSE_ERROR, $result);
    }

    public function testHandleException()
    {
        $extraParameterSalon = $this->prophesize(Event\ExtraParameter::class);
        $extraParameterSessionSalon = $this->prophesize(Event\ExtraParameter::class);
        $extraParameterApplication = $this->prophesize(Event\ExtraParameter::class);

        $extraParameterSalon->getValue()->willReturn('salon');
        $extraParameterSessionSalon->getValue()->willReturn('sessionSalon');
        $extraParameterApplication->getValue()->willReturn('application123');

        $this->extraParameterRepository
            ->findByEventAndType($this->event->reveal(), Type::TYPE_COMEXPOSIUM_SSO_APPLICATION)
            ->shouldBeCalled()
            ->willReturn($extraParameterApplication)
        ;
        $this->extraParameterRepository
            ->findByEventAndType($this->event->reveal(), Type::TYPE_COMEXPOSIUM_SSO_SALON)
            ->shouldBeCalled()
            ->willReturn($extraParameterSalon->reveal())
        ;
        $this->extraParameterRepository
            ->findByEventAndType($this->event->reveal(), Type::TYPE_COMEXPOSIUM_SSO_SESSION_SALON)
            ->shouldBeCalled()
            ->willReturn($extraParameterSessionSalon->reveal())
        ;

        $payload = [
            'email' => 'email@example.net',
            'fromSalon' => 'salon',
            'fromSessionSalon' => 'sessionSalon',
            'language' => 'fre-FR',
            'fromThirdParty' => 'application123',
        ];
        $this->httpAdapter->post(
            $this->comexposiumSsoCreateUserEndPoint,
            [],
            json_encode($payload)
        )->shouldBeCalled()
        ->willThrow(ServerErrorException::class);
        $this->localeConverter->formatLocale('fr')->shouldBeCalled()->willReturn('fre-FR');

        $handler = new CreateHandler(
            $this->httpAdapter->reveal(),
            $this->extraParameterRepository->reveal(),
            $this->comexposiumSsoCreateUserEndPoint,
            $this->localeConverter->reveal()
        );

        $result = $handler->handle(new Create($this->event->reveal(), 'email@example.net', 'fr'));

        $this->assertEquals(CreateHandler::RESPONSE_ERROR, $result);
    }
}
