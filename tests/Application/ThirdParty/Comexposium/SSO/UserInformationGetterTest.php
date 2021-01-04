<?php

namespace Proximum\Vimeet\Tests\Application\ThirdParty\Comexposium\SSO;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\HttpAdapterInterface;
use Proximum\Vimeet\Application\Command\Event\ExtraData\AddOrUpdate;
use Proximum\Vimeet\Application\Command\Event\ExtraData\AddOrUpdateHandler;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\Converter\RawUserDataToUserInformationViewConverter;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\LoginHandler;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\UserInformationGetter;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\View\UserInformationView;
use Proximum\Vimeet\Application\View\Adapter\Http\Response;
use Proximum\Vimeet\Domain\Event\ExtraData\Type as EventExtraDataType;
use Proximum\Vimeet\Domain\Event\ExtraParameter\Type;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\Event\ExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Event\ExtraParameterRepositoryInterface;

class UserInformationGetterTest extends TestCase
{
    /** @var string */
    private $email;

    /** @var string */
    private $locale;

    /** @var string */
    private $comexposiumGetUserEndpoint;

    /** @var ObjectProphecy */
    private $event;

    /** @var ObjectProphecy */
    private $extraParameterRepository;

    /** @var ObjectProphecy */
    private $httpAdapter;

    /** @var ObjectProphecy */
    private $loginHandler;

    /** @var ObjectProphecy */
    private $rawUserDataToUserInformationViewConverter;

    /** @var ObjectProphecy */
    private $extraDataRepository;

    /** @var ObjectProphecy */
    private $addOrUpdateHandler;

    public function setUp()
    {
        $this->event = $this->prophesize(Event::class);

        $this->comexposiumGetUserEndpoint = 'https://get-user-info-endpoint';
        $this->loginHandler = $this->prophesize(LoginHandler::class);
        $this->rawUserDataToUserInformationViewConverter = $this->prophesize(
            RawUserDataToUserInformationViewConverter::class
        );
        $this->httpAdapter = $this->prophesize(HttpAdapterInterface::class);
        $this->extraParameterRepository = $this->prophesize(ExtraParameterRepositoryInterface::class);
        $this->extraDataRepository = $this->prophesize(ExtraDataRepositoryInterface::class);
        $this->addOrUpdateHandler = $this->prophesize(AddOrUpdateHandler::class);

        $this->email = 'bruce.willis@example.net';
        $this->locale = 'fr';
    }

    public function testJwtTokenIsNullHandle()
    {
        $this->expectException(\LogicException::class);

        $this
            ->extraParameterRepository
            ->findByEventAndType(
                $this->event->reveal(),
                Type::TYPE_COMEXPOSIUM_SSO_APPLICATION
            )
            ->shouldBeCalled()
            ->willReturn(
                new Event\ExtraParameter(
                    $this->event->reveal(),
                    Type::TYPE_COMEXPOSIUM_SSO_APPLICATION,
                    'ssoApp',
                    'whatever-sso-app',
                    new \DateTime()
                )
            )
        ;

        $this
            ->extraDataRepository
            ->getExtraDataForEvent($this->event->reveal(), EventExtraDataType::COMEXPOSIUM_SSO_JWT_TOKEN)
            ->shouldBeCalled()
            ->willReturn(null)
        ;

        $this->loginHandler->loginAndGetJwtToken()->shouldBeCalled()->willReturn(null);

        $userInformationGetter = new UserInformationGetter(
            $this->loginHandler->reveal(),
            $this->rawUserDataToUserInformationViewConverter->reveal(),
            $this->httpAdapter->reveal(),
            $this->extraParameterRepository->reveal(),
            $this->extraDataRepository->reveal(),
            $this->addOrUpdateHandler->reveal(),
            $this->comexposiumGetUserEndpoint
        );
        $userInformationGetter->handle($this->event->reveal(), $this->email, $this->locale);
    }

    public function testExtraParameterIsNullHandle()
    {
        $this->expectException(\LogicException::class);

        $this->loginHandler->loginAndGetJwtToken()->shouldNotBeCalled();

        $this->extraParameterRepository
            ->findByEventAndType(
                $this->event->reveal(),
                Type::TYPE_COMEXPOSIUM_SSO_APPLICATION
            )
            ->shouldBeCalled()
            ->willReturn(null)
        ;

        $userInformationGetter = new UserInformationGetter(
            $this->loginHandler->reveal(),
            $this->rawUserDataToUserInformationViewConverter->reveal(),
            $this->httpAdapter->reveal(),
            $this->extraParameterRepository->reveal(),
            $this->extraDataRepository->reveal(),
            $this->addOrUpdateHandler->reveal(),
            $this->comexposiumGetUserEndpoint
        );
        $userInformationGetter->handle($this->event->reveal(), $this->email, $this->locale);
    }

    public function testHandleWithNoData()
    {
        $this
            ->extraDataRepository
            ->getExtraDataForEvent($this->event->reveal(), EventExtraDataType::COMEXPOSIUM_SSO_JWT_TOKEN)
            ->shouldBeCalled()
            ->willReturn(null)
        ;

        $this->loginHandler->loginAndGetJwtToken()->shouldBeCalled()->willReturn('whatever-jwt-token');

        $this
            ->addOrUpdateHandler
            ->handle(
                new AddOrUpdate(
                    $this->event->reveal(),
                    EventExtraDataType::COMEXPOSIUM_SSO_JWT_TOKEN,
                    'whatever-jwt-token'
                )
            )
            ->shouldBeCalled()
        ;

        $this->extraParameterRepository
            ->findByEventAndType(
                $this->event->reveal(),
                Type::TYPE_COMEXPOSIUM_SSO_APPLICATION
            )
            ->shouldBeCalled()
            ->willReturn(
                new Event\ExtraParameter(
                    $this->event->reveal(),
                    Type::TYPE_COMEXPOSIUM_SSO_APPLICATION,
                    'ssoApp',
                    'whatever-sso-app',
                    new \DateTime()
                )
            )
        ;

        $this->httpAdapter
            ->get(
                $this->comexposiumGetUserEndpoint,
                [
                    'accept' => 'application/json',
                    'content-type' => 'application/json',
                    'Authorization' => sprintf('Bearer %s', 'whatever-jwt-token'),
                    'query' => [
                        'appToken' => 'whatever-sso-app',
                    ],
                ]
            )
            ->shouldBeCalled()
            ->willReturn(new Response(200, ''))
        ;

        $userInformationGetter = new UserInformationGetter(
            $this->loginHandler->reveal(),
            $this->rawUserDataToUserInformationViewConverter->reveal(),
            $this->httpAdapter->reveal(),
            $this->extraParameterRepository->reveal(),
            $this->extraDataRepository->reveal(),
            $this->addOrUpdateHandler->reveal(),
            $this->comexposiumGetUserEndpoint
        );
        $result = $userInformationGetter->handle($this->event->reveal(), $this->email, $this->locale);

        $this->assertNull($result);
    }

    public function testHandleWithProfileData()
    {
        $this
            ->extraDataRepository
            ->getExtraDataForEvent($this->event->reveal(), EventExtraDataType::COMEXPOSIUM_SSO_JWT_TOKEN)
            ->shouldBeCalled()
            ->willReturn(null)
        ;

        $this->loginHandler->loginAndGetJwtToken()->shouldBeCalled()->willReturn('whatever-jwt-token');

        $this
            ->addOrUpdateHandler
            ->handle(
                new AddOrUpdate(
                    $this->event->reveal(),
                    EventExtraDataType::COMEXPOSIUM_SSO_JWT_TOKEN,
                    'whatever-jwt-token'
                )
            )
            ->shouldBeCalled()
        ;

        $this->extraParameterRepository
            ->findByEventAndType(
                $this->event->reveal(),
                Type::TYPE_COMEXPOSIUM_SSO_APPLICATION
            )
            ->shouldBeCalled()
            ->willReturn(
                new Event\ExtraParameter(
                    $this->event->reveal(),
                    Type::TYPE_COMEXPOSIUM_SSO_APPLICATION,
                    'ssoApp',
                    'whatever-sso-app',
                    new \DateTime()
                )
            )
        ;

        $this->httpAdapter
            ->get(
                $this->comexposiumGetUserEndpoint,
                [
                    'accept' => 'application/json',
                    'content-type' => 'application/json',
                    'Authorization' => sprintf('Bearer %s', 'whatever-jwt-token'),
                    'query' => [
                        'appToken' => 'whatever-sso-app',
                    ],
                ]
            )
            ->shouldBeCalled()
            ->willReturn(
                new Response(200, '{"result": {"data": {"profileData": {"firstname": "Bruce", "lastname": "Willis"}}}}')
            )
        ;

        $expectedResult = new UserInformationView($this->email, null, 'Bruce', 'Willis', null, null, $this->locale);

        $this->rawUserDataToUserInformationViewConverter
            ->convert(
                $this->email,
                $this->locale,
                [
                    'firstname' => 'Bruce',
                    'lastname' => 'Willis',
                ]
            )
            ->shouldBeCalled()
            ->willReturn($expectedResult)
        ;

        $userInformationGetter = new UserInformationGetter(
            $this->loginHandler->reveal(),
            $this->rawUserDataToUserInformationViewConverter->reveal(),
            $this->httpAdapter->reveal(),
            $this->extraParameterRepository->reveal(),
            $this->extraDataRepository->reveal(),
            $this->addOrUpdateHandler->reveal(),
            $this->comexposiumGetUserEndpoint
        );
        $result = $userInformationGetter->handle($this->event->reveal(), $this->email, $this->locale);

        $this->assertEquals($expectedResult, $result);
    }

    public function testHandleWithSavedJwtToken()
    {
        $this
            ->extraDataRepository
            ->getExtraDataForEvent($this->event->reveal(), EventExtraDataType::COMEXPOSIUM_SSO_JWT_TOKEN)
            ->shouldBeCalled()
            ->willReturn(
                new Event\ExtraData(
                    $this->event->reveal(),
                    EventExtraDataType::COMEXPOSIUM_SSO_JWT_TOKEN,
                    'saved-jwt-token',
                    new \DateTime()
                )
            )
        ;

        $this->loginHandler->loginAndGetJwtToken()->shouldNotBeCalled();

        $this->extraParameterRepository
            ->findByEventAndType(
                $this->event->reveal(),
                Type::TYPE_COMEXPOSIUM_SSO_APPLICATION
            )
            ->shouldBeCalled()
            ->willReturn(
                new Event\ExtraParameter(
                    $this->event->reveal(),
                    Type::TYPE_COMEXPOSIUM_SSO_APPLICATION,
                    'ssoApp',
                    'whatever-sso-app',
                    new \DateTime()
                )
            )
        ;

        $this
            ->httpAdapter
            ->get(
                $this->comexposiumGetUserEndpoint,
                [
                    'accept' => 'application/json',
                    'content-type' => 'application/json',
                    'Authorization' => sprintf('Bearer %s', 'saved-jwt-token'),
                    'query' => [
                        'appToken' => 'whatever-sso-app',
                    ],
                ]
            )
            ->shouldBeCalled()
            ->willReturn(
                new Response(200, '{"result": {"data": {"profileData": {"firstname": "Bruce", "lastname": "Willis"}}}}')
            )
        ;

        $expectedResult = new UserInformationView($this->email, null, 'Bruce', 'Willis', null, null, $this->locale);

        $this->rawUserDataToUserInformationViewConverter
            ->convert(
                $this->email,
                $this->locale,
                [
                    'firstname' => 'Bruce',
                    'lastname' => 'Willis',
                ]
            )
            ->shouldBeCalled()
            ->willReturn($expectedResult)
        ;

        $userInformationGetter = new UserInformationGetter(
            $this->loginHandler->reveal(),
            $this->rawUserDataToUserInformationViewConverter->reveal(),
            $this->httpAdapter->reveal(),
            $this->extraParameterRepository->reveal(),
            $this->extraDataRepository->reveal(),
            $this->addOrUpdateHandler->reveal(),
            $this->comexposiumGetUserEndpoint
        );
        $result = $userInformationGetter->handle($this->event->reveal(), $this->email, $this->locale);

        $this->assertEquals($expectedResult, $result);
    }

    public function testHandleWithExpiredJwtToken()
    {
        $this
            ->extraDataRepository
            ->getExtraDataForEvent($this->event->reveal(), EventExtraDataType::COMEXPOSIUM_SSO_JWT_TOKEN)
            ->shouldBeCalled()
            ->willReturn(
                new Event\ExtraData(
                    $this->event->reveal(),
                    EventExtraDataType::COMEXPOSIUM_SSO_JWT_TOKEN,
                    'expired-jwt-token',
                    new \DateTime()
                )
            )
        ;

        $this->loginHandler->loginAndGetJwtToken()->shouldBeCalled()->willReturn('fresh-jwt-token');

        $this
            ->addOrUpdateHandler
            ->handle(
                new AddOrUpdate(
                    $this->event->reveal(),
                    EventExtraDataType::COMEXPOSIUM_SSO_JWT_TOKEN,
                    'fresh-jwt-token'
                )
            )
            ->shouldBeCalled()
        ;

        $this
            ->extraParameterRepository
            ->findByEventAndType(
                $this->event->reveal(),
                Type::TYPE_COMEXPOSIUM_SSO_APPLICATION
            )
            ->shouldBeCalled()
            ->willReturn(
                new Event\ExtraParameter(
                    $this->event->reveal(),
                    Type::TYPE_COMEXPOSIUM_SSO_APPLICATION,
                    'ssoApp',
                    'whatever-sso-app',
                    new \DateTime()
                )
            )
        ;

        $this
            ->httpAdapter
            ->get(
                $this->comexposiumGetUserEndpoint,
                [
                    'accept' => 'application/json',
                    'content-type' => 'application/json',
                    'Authorization' => sprintf('Bearer %s', 'expired-jwt-token'), // expired token
                    'query' => [
                        'appToken' => 'whatever-sso-app',
                    ],
                ]
            )
            ->shouldBeCalled()
            ->willReturn(
                new Response(401, '{}')
            )
        ;

        $this
            ->httpAdapter
            ->get(
                $this->comexposiumGetUserEndpoint,
                [
                    'accept' => 'application/json',
                    'content-type' => 'application/json',
                    'Authorization' => sprintf('Bearer %s', 'fresh-jwt-token'), // fresh token
                    'query' => [
                        'appToken' => 'whatever-sso-app',
                    ],
                ]
            )
            ->shouldBeCalled()
            ->willReturn(
                new Response(200, '{"result": {"data": {"profileData": {"firstname": "Bruce", "lastname": "Willis"}}}}')
            )
        ;

        $expectedResult = new UserInformationView($this->email, null, 'Bruce', 'Willis', null, null, $this->locale);

        $this->rawUserDataToUserInformationViewConverter
            ->convert(
                $this->email,
                $this->locale,
                [
                    'firstname' => 'Bruce',
                    'lastname' => 'Willis',
                ]
            )
            ->shouldBeCalled()
            ->willReturn($expectedResult)
        ;

        $userInformationGetter = new UserInformationGetter(
            $this->loginHandler->reveal(),
            $this->rawUserDataToUserInformationViewConverter->reveal(),
            $this->httpAdapter->reveal(),
            $this->extraParameterRepository->reveal(),
            $this->extraDataRepository->reveal(),
            $this->addOrUpdateHandler->reveal(),
            $this->comexposiumGetUserEndpoint
        );
        $result = $userInformationGetter->handle($this->event->reveal(), $this->email, $this->locale);

        $this->assertEquals($expectedResult, $result);
    }
}
