<?php

namespace Proximum\Vimeet\Tests\Application\ThirdParty\Oauth2;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\SessionInterface;
use Proximum\Vimeet\Application\Command\Participant\ConvertToParticipant;
use Proximum\Vimeet\Application\Command\Participant\ConvertToParticipantHandler;
use Proximum\Vimeet\Application\Components\Sheet\SheetGuesser;
use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Application\Exception\Sheet\SheetNotFoundException;
use Proximum\Vimeet\Application\ThirdParty\Oauth2\AuthenticationException;
use Proximum\Vimeet\Application\ThirdParty\Oauth2\GetOrCreateUser;
use Proximum\Vimeet\Application\ThirdParty\Oauth2\GetOrCreateUserHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;

class GetOrCreateUserHandlerTest extends TestCase
{
    /** @var ObjectProphecy|ConvertToParticipantHandler */
    private $convertToParticipantHandler;

    /** @var ObjectProphecy|TemplateDataFactory */
    private $templateDataFactory;

    /** @var ObjectProphecy|SessionInterface */
    private $session;

    /** @var ObjectProphecy|SheetGuesser */
    private $sheetGuesser;

    /** @var ObjectProphecy|UserRepositoryInterface */
    private $userRepository;

    /** @var ObjectProphecy|TypeRepositoryInterface */
    private $typeRepository;

    /** @var GetOrCreateUserHandler */
    private $getOrCreateUserHandler;

    /** @var ObjectProphecy|Event */
    private $event;

    protected function setUp()
    {
        $this->event = $this->prophesize(Event::class);

        $this->convertToParticipantHandler = $this->prophesize(ConvertToParticipantHandler::class);
        $this->templateDataFactory = $this->prophesize(TemplateDataFactory::class);
        $this->session = $this->prophesize(SessionInterface::class);
        $this->sheetGuesser = $this->prophesize(SheetGuesser::class);
        $this->userRepository = $this->prophesize(UserRepositoryInterface::class);
        $this->typeRepository = $this->prophesize(TypeRepositoryInterface::class);

        $this->getOrCreateUserHandler = new GetOrCreateUserHandler(
            $this->convertToParticipantHandler->reveal(),
            $this->templateDataFactory->reveal(),
            $this->session->reveal(),
            $this->sheetGuesser->reveal(),
            $this->userRepository->reveal(),
            $this->typeRepository->reveal()
        );
    }

    public function testUserNotExistsAndNoTypeGiven()
    {
        $this->expectException(AuthenticationException::class);
        $this->session->getFromFlashBag('register_type')->shouldBeCalled()->willReturn([]);
        $this->userRepository->findByEmail('rene@descartes.fr')->shouldBeCalled()->willReturn(null);
        $getOrCreateUser = new GetOrCreateUser($this->event->reveal(), 'fr', 'rene@descartes.fr', 'René', 'Descartes');
        $this->getOrCreateUserHandler->handle($getOrCreateUser);
    }

    public function testUserExistsAndNoTypeGiven()
    {
        $user = $this->prophesize(User::class);
        $this->session->getFromFlashBag('register_type')->shouldBeCalled()->willReturn([]);
        $this->userRepository->findByEmail('rene@descartes.fr')->shouldBeCalled()->willReturn($user->reveal());
        $getOrCreateUser = new GetOrCreateUser($this->event->reveal(), 'fr', 'rene@descartes.fr', 'René', 'Descartes');
        $this->assertEquals($user->reveal(), $this->getOrCreateUserHandler->handle($getOrCreateUser));
    }

    public function testUserExistsAndTypeGiven()
    {
        $user = $this->prophesize(User::class);
        $this->session->getFromFlashBag('register_type')->shouldBeCalled()->willReturn([42]);
        $this->userRepository->findByEmail('rene@descartes.fr')->shouldBeCalled()->willReturn($user->reveal());

        $type = $this->prophesize(Type::class);
        $this->typeRepository->getById(42)->shouldBeCalled()->willReturn($type->reveal());

        $sheet = $this->prophesize(Sheet::class);
        $this->sheetGuesser
            ->getUserSheet($user, $this->event->reveal(), 'fr')
            ->shouldBeCalled()
            ->willReturn([$sheet->reveal()])
        ;

        $getOrCreateUser = new GetOrCreateUser($this->event->reveal(), 'fr', 'rene@descartes.fr', 'René', 'Descartes');
        $this->assertEquals($user->reveal(), $this->getOrCreateUserHandler->handle($getOrCreateUser));
    }

    public function testUserNotExistsAndTypeGiven()
    {
        $this->session->getFromFlashBag('register_type')->shouldBeCalled()->willReturn([42]);
        $this->userRepository->findByEmail('rene@descartes.fr')->shouldBeCalled()->willReturn(null);

        $type = $this->prophesize(Type::class);
        $this->typeRepository->getById(42)->shouldBeCalled()->willReturn($type->reveal());

        $registrationTemplateData = $this->prophesize(TemplateData::class);
        $this->templateDataFactory
            ->createRegistrationFromType($type, null)
            ->shouldBeCalled()
            ->willReturn($registrationTemplateData->reveal())
        ;

        $sheetTemplateData = $this->prophesize(TemplateData::class);
        $this->templateDataFactory
            ->createSheetTemplateFromType($type)
            ->shouldBeCalled()
            ->willReturn($sheetTemplateData->reveal())
        ;

        $user = $this->prophesize(User::class);
        $participant = $this->prophesize(Participant::class);
        $participant->getUser()->shouldBeCalled()->willReturn($user->reveal());

        $this->convertToParticipantHandler
            ->handle(
                new ConvertToParticipant(
                    $this->event->reveal(),
                    $type->reveal(),
                    'rene@descartes.fr',
                    'fr',
                    [
                        Tag::PARTICIPANT_FIRSTNAME => 'René',
                        Tag::PARTICIPANT_LASTNAME => 'Descartes',
                    ],
                    $registrationTemplateData->reveal(),
                    $sheetTemplateData->reveal()
                )
            )
            ->shouldBeCalled()
            ->willReturn($participant->reveal())
        ;

        $getOrCreateUser = new GetOrCreateUser($this->event->reveal(), 'fr', 'rene@descartes.fr', 'René', 'Descartes');
        $this->assertEquals($user->reveal(), $this->getOrCreateUserHandler->handle($getOrCreateUser));
    }

    public function testUserHasNotSheet()
    {
        $user = $this->prophesize(User::class);
        $this->session->getFromFlashBag('register_type')->shouldBeCalled()->willReturn([42]);
        $this->userRepository->findByEmail('rene@descartes.fr')->shouldBeCalled()->willReturn($user->reveal());

        $type = $this->prophesize(Type::class);
        $this->typeRepository->getById(42)->shouldBeCalled()->willReturn($type->reveal());

        $this->sheetGuesser
            ->getUserSheet($user, $this->event->reveal(), 'fr')
            ->shouldBeCalled()
            ->willThrow(SheetNotFoundException::class)
        ;

        $participant = $this->prophesize(Participant::class);
        $participant->getUser()->shouldBeCalled()->willReturn($user->reveal());

        $registrationTemplateData = $this->prophesize(TemplateData::class);
        $this->templateDataFactory
            ->createRegistrationFromType($type, null)
            ->shouldBeCalled()
            ->willReturn($registrationTemplateData->reveal())
        ;

        $sheetTemplateData = $this->prophesize(TemplateData::class);
        $this->templateDataFactory
            ->createSheetTemplateFromType($type)
            ->shouldBeCalled()
            ->willReturn($sheetTemplateData->reveal())
        ;

        $this->convertToParticipantHandler
            ->handle(
                new ConvertToParticipant(
                    $this->event->reveal(),
                    $type->reveal(),
                    'rene@descartes.fr',
                    'fr',
                    [
                        Tag::PARTICIPANT_FIRSTNAME => 'René',
                        Tag::PARTICIPANT_LASTNAME => 'Descartes',
                    ],
                    $registrationTemplateData->reveal(),
                    $sheetTemplateData->reveal()
                )
            )
            ->shouldBeCalled()
            ->willReturn($participant->reveal())
        ;

        $getOrCreateUser = new GetOrCreateUser($this->event->reveal(), 'fr', 'rene@descartes.fr', 'René', 'Descartes');
        $this->assertEquals($user->reveal(), $this->getOrCreateUserHandler->handle($getOrCreateUser));
    }
}
