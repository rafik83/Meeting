<?php

namespace Proximum\Vimeet\Tests\Application\Query\Navigation\Submenu;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Components\Navigation\Category;
use Proximum\Vimeet\Application\Query\Navigation\Submenu\UserCtaSubmenuViewQuery;
use Proximum\Vimeet\Application\Query\Navigation\Submenu\UserCtaSubmenuViewQueryHandler;
use Proximum\Vimeet\Application\View\Navigation\SubmenuButtonView;
use Proximum\Vimeet\Domain\Event\ExtraParameter\Type as ExtraParameterType;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Event\ExtraParameterRepositoryInterface;

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
        $extraParameter = $this->prophesize(Event\ExtraParameter::class);
        $extraParameter->getValue()->shouldBeCalled()->willReturn('
        {
             "link": "https://example.net/%userId%/%userEmail%/%participantId%",
             "concerned_type_ids": [689, 746, 747, 748, 749, 750],
             "button-label": {
             "fr": "Mon bouton",
             "en": "My button"
             }
        }');
        $extraParameterRepository->findByEventAndType($event->reveal(), ExtraParameterType::TYPE_CUSTOM_BUTTON)->shouldBeCalled()->willReturn($extraParameter->reveal());

        $userCtaSubmenuViewQueryHandler = new UserCtaSubmenuViewQueryHandler(
            $extraParameterRepository->reveal()
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
            'https://example.net/42/test%40yahoo.fr/123',
            false,
            null,
            false,
            ['target' => '_blank']
    );

        $this->assertEquals($expectedCustomButtonSubmenuButtonView, $result);
    }

    public function testCustomButtonNull(): void
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

        $userCtaSubmenuViewQueryHandler = new UserCtaSubmenuViewQueryHandler(
            $extraParameterRepository->reveal()
        );

        $result = $userCtaSubmenuViewQueryHandler->handle(
            new UserCtaSubmenuViewQuery(
                $user->reveal(),
                $event->reveal(),
                'fr',
                $sheet->reveal()
            )
        );

        $this->assertEquals(null, $result);
    }
}
