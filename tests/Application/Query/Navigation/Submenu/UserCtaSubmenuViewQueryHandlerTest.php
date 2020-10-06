<?php

namespace Proximum\Vimeet\Tests\Application\Query\Navigation\Submenu;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Components\Navigation\Category;
use Proximum\Vimeet\Application\Query\Navigation\Submenu\UserCtaSubmenuViewQuery;
use Proximum\Vimeet\Application\Query\Navigation\Submenu\UserCtaSubmenuViewQueryHandler;
use Proximum\Vimeet\Application\View\Navigation\SubmenuButtonView;
use Proximum\Vimeet\Domain\Event\ExtraParameter\Type as ExtraParameterType;
use Proximum\Vimeet\Domain\User\Event\ExtraData\Type as TypeExtraData;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Event\ExtraParameterRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\User\Event\ExtraDataRepositoryInterface;

class UserCtaSubmenuViewQueryHandlerTest extends TestCase
{
    public function testCustomButton(): void
    {
        $participant = $this->prophesize(Participant::class);
        $participant->getId()->shouldBeCalled()->willReturn(123);
        $user = $this->prophesize(User::class);
        $user->getId()->willReturn(42);
        $user->getEmail()->willReturn('test@yahoo.fr');
        $event = $this->prophesize(Event::class);
        $type = $this->prophesize(Type::class);
        $type->getId()->shouldBeCalled()->willReturn(746);
        $sheet = $this->prophesize(Sheet::class);
        $sheet->getType()->shouldBeCalled()->willReturn($type->reveal());
        $sheet->getUserParticipant($user->reveal())->willReturn($participant->reveal());

        $extraParameterRepository = $this->prophesize(ExtraParameterRepositoryInterface::class);
        $extraData = $this->prophesize(User\Event\ExtraData::class);
        $extraData->getValue()->shouldBeCalled()->willReturn(123456789);
        $extraParameter = $this->prophesize(Event\ExtraParameter::class);
        $extraParameter->getValue()->shouldBeCalled()->willReturn('
        {
             "link": "https://example.net/%userId%/%userEmail%/%participantId%/%techEventIdContact%",
             "concerned_type_ids": [689, 746, 747, 748, 749, 750],
             "button-label": {
             "fr": "Mon bouton",
             "en": "My button"
             }
        }');
        $extraParameterRepository->findByEventAndType($event->reveal(), ExtraParameterType::TYPE_CUSTOM_BUTTON)->shouldBeCalled()->willReturn($extraParameter->reveal());

        $extraDataRepository = $this->prophesize(ExtraDataRepositoryInterface::class);
        $extraDataRepository->getExtraDataForEventNameAndUser($event->reveal(),TypeExtraData::IMPORTED_FROM_TECH_EVENT, $user->reveal())->shouldBeCalled()->willReturn($extraData->reveal());

        $userCtaSubmenuViewQueryHandler = new UserCtaSubmenuViewQueryHandler(
            $extraParameterRepository->reveal(),
            $extraDataRepository->reveal()
        );

        $result = $userCtaSubmenuViewQueryHandler->handle(
            new UserCtaSubmenuViewQuery(
                $user->reveal(),
                $event->reveal(),
                'fr',
                $sheet->reveal()
            )
        );

        $expectedCustomButtonSubmenuButtonView = new SubmenuButtonView(
        Category::CUSTOM_BUTTON_ICON,
            'Mon bouton',
            'https://example.net/42/test%40yahoo.fr/123/123456789',
            false,
            null,
            false,
            ['target' => '_blank']
    );

        self::assertEquals($expectedCustomButtonSubmenuButtonView, $result);
    }

    public function testCustomButtonWhenParticipantIdNotNeeded(): void
    {
        $user = $this->prophesize(User::class);
        $user->getId()->willReturn(42);
        $user->getEmail()->willReturn('test@yahoo.fr');
        $event = $this->prophesize(Event::class);
        $type = $this->prophesize(Type::class);
        $type->getId()->shouldBeCalled()->willReturn(746);
        $sheet = $this->prophesize(Sheet::class);
        $sheet->getType()->shouldBeCalled()->willReturn($type->reveal());
        $sheet->getUserParticipant($user->reveal())->willReturn(null);

        $extraParameterRepository = $this->prophesize(ExtraParameterRepositoryInterface::class);
        $extraParameter = $this->prophesize(Event\ExtraParameter::class);
        $extraParameter->getValue()->shouldBeCalled()->willReturn('
        {
             "link": "https://example.net/%userId%/%userEmail%",
             "concerned_type_ids": [689, 746, 747, 748, 749, 750],
             "button-label": {
             "fr": "Mon bouton",
             "en": "My button"
             }
        }');
        $extraParameterRepository->findByEventAndType($event->reveal(), ExtraParameterType::TYPE_CUSTOM_BUTTON)->shouldBeCalled()->willReturn($extraParameter->reveal());

        $extraDataRepository = $this->prophesize(ExtraDataRepositoryInterface::class);

        $userCtaSubmenuViewQueryHandler = new UserCtaSubmenuViewQueryHandler(
            $extraParameterRepository->reveal(),
            $extraDataRepository->reveal()
        );

        $result = $userCtaSubmenuViewQueryHandler->handle(
            new UserCtaSubmenuViewQuery(
                $user->reveal(),
                $event->reveal(),
                'fr',
                $sheet->reveal()
            )
        );

        $expectedCustomButtonSubmenuButtonView = new SubmenuButtonView(
            Category::CUSTOM_BUTTON_ICON,
            'Mon bouton',
            'https://example.net/42/test%40yahoo.fr',
            false,
            null,
            false,
            ['target' => '_blank']
        );

        self::assertEquals($expectedCustomButtonSubmenuButtonView, $result);
    }

    public function testCustomButtonNullWhenTypeNotConcerned(): void
    {
        $type = $this->prophesize(Type::class);
        $type->getId()->shouldBeCalled()->willReturn(701);
        $sheet = $this->prophesize(Sheet::class);
        $sheet->getType()->shouldBeCalled()->willReturn($type->reveal());
        $user = $this->prophesize(User::class);
        $user->getEmail()->willReturn('test@yahoo.fr');
        $event = $this->prophesize(Event::class);

        $extraParameterRepository = $this->prophesize(ExtraParameterRepositoryInterface::class);
        $extraParameter = $this->prophesize(Event\ExtraParameter::class);
        $extraParameter->getValue()->shouldBeCalled()->willReturn('
        {
             "link": "https://example.net/%userId%/%userEmail%",
             "concerned_type_ids": [689, 746, 747, 748, 749, 750],
             "button-label": {
             "fr": "Mon bouton",
             "en": "My button"
             }
        }');
        $extraParameterRepository->findByEventAndType($event->reveal(), ExtraParameterType::TYPE_CUSTOM_BUTTON)->shouldBeCalled()->willReturn($extraParameter->reveal());

        $extraDataRepository = $this->prophesize(ExtraDataRepositoryInterface::class);

        $userCtaSubmenuViewQueryHandler = new UserCtaSubmenuViewQueryHandler(
            $extraParameterRepository->reveal(),
            $extraDataRepository->reveal()
        );

        $result = $userCtaSubmenuViewQueryHandler->handle(
            new UserCtaSubmenuViewQuery(
                $user->reveal(),
                $event->reveal(),
                'fr',
                $sheet->reveal()
            )
        );

        self::assertEquals(null, $result);
    }

    public function testCustomButtonNullWhenNotConfigured(): void
    {
        $sheet = $this->prophesize(Sheet::class);
        $user = $this->prophesize(User::class);
        $event = $this->prophesize(Event::class);

        $extraParameterRepository = $this->prophesize(ExtraParameterRepositoryInterface::class);
        $extraParameterRepository->findByEventAndType($event->reveal(), ExtraParameterType::TYPE_CUSTOM_BUTTON)->shouldBeCalled()->willReturn(null);

        $extraDataRepository = $this->prophesize(ExtraDataRepositoryInterface::class);

        $userCtaSubmenuViewQueryHandler = new UserCtaSubmenuViewQueryHandler(
            $extraParameterRepository->reveal(),
            $extraDataRepository->reveal()
        );

        $result = $userCtaSubmenuViewQueryHandler->handle(
            new UserCtaSubmenuViewQuery(
                $user->reveal(),
                $event->reveal(),
                'fr',
                $sheet->reveal()
            )
        );

        self::assertNull($result);
    }

    public function testCustomButtonNullWhenNotParticipant(): void
    {
        $user = $this->prophesize(User::class);
        $user->getId()->willReturn(42);
        $user->getEmail()->willReturn('test@yahoo.fr');
        $event = $this->prophesize(Event::class);
        $type = $this->prophesize(Type::class);
        $type->getId()->shouldBeCalled()->willReturn(746);
        $sheet = $this->prophesize(Sheet::class);
        $sheet->getType()->shouldBeCalled()->willReturn($type->reveal());
        $sheet->getUserParticipant($user->reveal())->willReturn(null);

        $extraParameterRepository = $this->prophesize(ExtraParameterRepositoryInterface::class);
        $extraParameter = $this->prophesize(Event\ExtraParameter::class);
        $extraParameter->getValue()->shouldBeCalled()->willReturn('
        {
             "link": "https://example.net/%userId%/%userEmail%/%participantId%/%techEventIdContact%",
             "concerned_type_ids": [689, 746, 747, 748, 749, 750],
             "button-label": {
             "fr": "Mon bouton",
             "en": "My button"
             }
        }');
        $extraParameterRepository->findByEventAndType($event->reveal(), ExtraParameterType::TYPE_CUSTOM_BUTTON)->shouldBeCalled()->willReturn($extraParameter->reveal());

        $extraDataRepository = $this->prophesize(ExtraDataRepositoryInterface::class);

        $userCtaSubmenuViewQueryHandler = new UserCtaSubmenuViewQueryHandler(
            $extraParameterRepository->reveal(),
            $extraDataRepository->reveal()
        );

        $result = $userCtaSubmenuViewQueryHandler->handle(
            new UserCtaSubmenuViewQuery(
                $user->reveal(),
                $event->reveal(),
                'fr',
                $sheet->reveal()
            )
        );

        self::assertNull($result);
    }
}
