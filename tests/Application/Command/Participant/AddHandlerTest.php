<?php

namespace Proximum\Vimeet\Tests\Application\Command\Participant;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Command\Participant\Add;
use Proximum\Vimeet\Application\Command\Participant\AddHandler;
use Proximum\Vimeet\Application\Command\Participant\AddResult;
use Proximum\Vimeet\Application\Command\Participant\UpdateParticipantProductQuantity;
use Proximum\Vimeet\Application\Command\Participant\UpdateParticipantProductQuantityHandler;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Participant\ParticipantAddedEvent;
use Proximum\Vimeet\Application\Event\Sheet\SheetUpdatedEvent;
use Proximum\Vimeet\Application\Exception\Sheet\ParticipantAlreadyExistException;
use Proximum\Vimeet\Application\View\Package\ParticipantProductView;
use Proximum\Vimeet\Domain\Account\Synchronizer;
use Proximum\Vimeet\Domain\Model\Package;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Domain\Template;
use Proximum\Vimeet\Domain\UserEvent\TypeResolver;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class AddHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $sheetRepository;

    /** @var ObjectProphecy */
    private $templateDataFactory;

    /** @var ObjectProphecy */
    private $eventDispatcher;

    /** @var ObjectProphecy */
    private $typeResolver;

    /** @var ObjectProphecy */
    private $accountSynchronizer;

    /** @var ObjectProphecy */
    private $updateParticipantProductQuantityHandler;

    /** @var ObjectProphecy */
    private $userRepository;

    /** @var ObjectProphecy */
    private $participantRepository;

    public function setUp()
    {
        $this->userRepository = $this->prophesize(UserRepositoryInterface::class);
        $this->participantRepository = $this->prophesize(ParticipantRepositoryInterface::class);
        $this->sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $this->templateDataFactory = $this->prophesize(Template\TemplateDataFactory::class);
        $this->eventDispatcher = $this->prophesize(DelayedEventDispatcher::class);
        $this->typeResolver = $this->prophesize(TypeResolver::class);
        $this->accountSynchronizer = $this->prophesize(Synchronizer::class);
        $this->updateParticipantProductQuantityHandler = $this->prophesize(UpdateParticipantProductQuantityHandler::class);
    }

    public function testHandleWhenUserNotExists()
    {
        $now   = new \DateTime();
        $event = EventFactory::createEvent();
        $type  = new Type($event);
        $user  = new User('email@email.com', 'salt', 'password', 'fr');
        $sheet = new Sheet($event, $type, [], $user, $now);
        $registrationDate = new \DateTime();

        $planProduct        = Product::createPlan($event, 'plan', '', 100, 20, 10, 40);
        $participantProduct = Product::createParticipant($event, 'participant', 50, 20, 10);

        $participantProductView = new ParticipantProductView(
            15,
            'title',
            'description',
            134,
            'EUR',
            'ati',
            5,
            1,
            true,
            1,
            true
        );

        $package = new Package($event, 'My package', $now);
        $package->enable(true, true, true);
        $package->setPlans([$planProduct]);
        $package->setParticipants([$participantProduct]);
        $type->setPackage($package);

        $expectedSheet       = new Sheet($event, $type, [], $user, $now);
        $expectedUser        = new User('test@test.com', '', '', 'fr');
        $expectedParticipant = new Participant(
            $expectedSheet,
            $expectedUser,
            [
                '541f84d4' => [
                    'text' => 'jean',
                ],
                '838197c7' => [
                    'text' => 'truc',
                ],
            ],
            false,
            $now
        );
        $expectedSheet->addParticipant($expectedParticipant);

        $this->userRepository->findByEmail('test@test.com')->shouldBeCalled()->willReturn(null);
        $this->userRepository->add($expectedUser)->shouldBeCalled();

        $this->participantRepository->add(
            Argument::that(
                function (Participant $participant) use ($expectedParticipant) {
                    return $participant->getData() === $expectedParticipant->getData();
                }
            )
        )->shouldBeCalled();

        $this->eventDispatcher->dispatch(
            Events::PARTICIPANT_ADDED,
            new ParticipantAddedEvent($expectedParticipant, $user)
        )->shouldBeCalled();
        $sheetUpdatedEvent = new SheetUpdatedEvent($sheet);
        $this->eventDispatcher->dispatch(Events::SHEET_UPDATED, $sheetUpdatedEvent)->shouldBeCalled();

        $templateData  = new Template\TemplateData('root', [], 'fr', 'fr');
        $block         = new Template\Block('12', [], 'fr', 'fr');
        $editableText1 = new Template\TemplateObject\EditableText(
            '69b3cde1',
            'editable-text', [
            'tags' => ['participant_firstname', 'participant_data'],
        ], 'fr', 'fr'
        );
        $editableText1->setContentValue('truc');
        $editableText2 = new Template\TemplateObject\EditableText(
            '69b3cde2',
            'editable-text', [
            'tags' => ['participant_lastname', 'participant_data'],
        ], 'fr', 'fr'
        );
        $editableText2->setContentValue('bidule');

        $block->addChild(1, '541f84d4', $editableText1);
        $block->addChild(1, '838197c7', $editableText2);
        $templateData->addChild(0, '811f6edf', $block);
        $this->templateDataFactory
            ->createRegistrationFromType($type, 'fr')
            ->shouldBeCalled()
            ->willReturn($templateData)
        ;

        $this->updateParticipantProductQuantityHandler
            ->handle(new UpdateParticipantProductQuantity($sheet, $expectedParticipant, 15))
            ->shouldBeCalled()
        ;

        $add = new Add($sheet, 'fr', $user, [$participantProductView]);
        $add->email = 'test@test.com';
        $add->firstName = 'jean';
        $add->lastName  = 'truc';

        $handler = new AddHandler(
            $this->userRepository->reveal(),
            $this->participantRepository->reveal(),
            $this->sheetRepository->reveal(),
            $this->templateDataFactory->reveal(),
            $this->eventDispatcher->reveal(),
            $this->updateParticipantProductQuantityHandler->reveal(),
            $this->typeResolver->reveal(),
            $this->accountSynchronizer->reveal(),
            $now
        );

        $this->assertEquals(new AddResult($expectedParticipant), $handler->handle($add));
    }

    public function testHandleWhenUserExists()
    {
        $this->expectException(ParticipantAlreadyExistException::class);

        $now  = new \DateTime();
        $event = EventFactory::createEvent();
        $type = new Type($event);
        $user = new User('test@test.com', '__SALT__', 'password', 'fr');
        $user2 = new User('test2@test.com', '__SALT__', 'password', 'fr');
        $sheet = new Sheet($event, $type, [], $user, $now);
        $participant = new Participant(
            $sheet,
            $user2,
            [
                '541f84d4' => [
                    'text' => 'jean',
                ],
                '838197c7' => [
                    'text' => 'truc',
                ],
            ],
            false,
            $now
        );
        $sheet->addParticipant($participant);

        $this->userRepository->findByEmail('test2@test.com')->shouldBeCalled()->willReturn($user2);

        $templateData  = new Template\TemplateData('root', [], 'fr', 'fr');
        $block         = new Template\Block('12', [], 'fr', 'fr');
        $editableText1 = new Template\TemplateObject\EditableText(
            '69b3cde1',
            'editable-text', [
            'tags' => ['participant_firstname', 'participant_data'],
        ], 'fr', 'fr'
        );
        $editableText1->setContentValue('truc');
        $editableText2 = new Template\TemplateObject\EditableText(
            '69b3cde2',
            'editable-text', [
            'tags' => ['participant_lastname', 'participant_data'],
        ], 'fr', 'fr'
        );
        $editableText2->setContentValue('bidule');

        $block->addChild(1, '541f84d4', $editableText1);
        $block->addChild(1, '838197c7', $editableText2);
        $templateData->addChild(0, '811f6edf', $block);

        $this->templateDataFactory->createRegistrationFromType($type, 'fr')->shouldNotBeCalled();

        $add            = new Add($sheet, 'fr', $user);
        $add->email     = 'test2@test.com';
        $add->firstName = 'jean';
        $add->lastName  = 'truc';

        $handler = new AddHandler(
            $this->userRepository->reveal(),
            $this->participantRepository->reveal(),
            $this->sheetRepository->reveal(),
            $this->templateDataFactory->reveal(),
            $this->eventDispatcher->reveal(),
            $this->updateParticipantProductQuantityHandler->reveal(),
            $this->typeResolver->reveal(),
            $this->accountSynchronizer->reveal(),
            $now
        );

        $handler->handle($add);
    }
}
