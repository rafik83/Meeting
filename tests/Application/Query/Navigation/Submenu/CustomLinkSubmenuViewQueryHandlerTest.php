<?php


namespace Proximum\Vimeet\Tests\Application\Query\Navigation\Submenu;


use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\UriTemplateInterface;
use Proximum\Vimeet\Application\Query\Navigation\Submenu\CustomLinkSubmenuViewQuery;
use Proximum\Vimeet\Application\Query\Navigation\Submenu\CustomLinkSubmenuViewQueryHandler;
use Proximum\Vimeet\Application\View\Navigation\SubmenuButtonView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\User\Event\ExtraData;
use Proximum\Vimeet\Domain\Repository\Event\CustomLinkRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\User\Event\ExtraDataRepositoryInterface;

class CustomLinkSubmenuViewQueryHandlerTest extends TestCase
{
    public function testCustomButton(): void
    {
        $customLinkRepository = $this->prophesize(CustomLinkRepositoryInterface::class);
        $uriTemplate = $this->prophesize(UriTemplateInterface::class);
        $extraDataRepository = $this->prophesize(ExtraDataRepositoryInterface::class);
        $participant = $this->prophesize(Participant::class);
        $participant->getId()->shouldBeCalled()->willReturn(123);
        $user = $this->prophesize(User::class);
        $user->getId()->shouldBeCalled()->willReturn(42);
        $user->getEmail()->willReturn('test@yahoo.fr');
        $event = $this->prophesize(Event::class);
        $type = $this->prophesize(Type::class);
        $sheet = $this->prophesize(Sheet::class);
        $sheet->getId()->shouldBeCalled()->willReturn(123456);
        $sheet->getType()->shouldBeCalled()->willReturn($type->reveal());
        $sheet->getUserParticipant($user->reveal())->shouldBeCalled()->willReturn($participant->reveal());
        $customLink = $this->prophesize(Event\CustomLink::class);
        $customLink->getUrl('fr')->shouldBeCalled()->willReturn('https://example.net/42/test@yahoo.fr/{participantId}/25f9e794323b453885f5181f1b624d0b/746');
        $customLink->getIconName()->shouldBeCalled()->willReturn('icon-Panier_2');
        $customLink->getLabel('fr')->shouldBeCalled()->willReturn('Mon bouton');
        $customLink->getIconColor()->shouldBeCalled()->willReturn('#FF0000');
        $customLink->getLabelColor()->shouldBeCalled()->willReturn('#FFFFFF');
        $customLink->getButtonColor()->shouldBeCalled()->willReturn('#000000');
        $uriTemplate->render(
            'https://example.net/42/test@yahoo.fr/{participantId}/25f9e794323b453885f5181f1b624d0b/746',
            [
                'userId' => 42,
                'userEmail' => 'test%40yahoo.fr',
                'participantId' => 123,
                'sheetId' => 123456,
                'techEventIdContact'=> null
            ]
        )->shouldBeCalled()->willReturn('https://google.fr');

        $customLinkRepository->findByType($type->reveal())
            ->shouldBeCalled()
            ->willReturn([$customLink->reveal()]);

        $customLinkSubmenuViewQueryHandler = new CustomLinkSubmenuViewQueryHandler(
            $customLinkRepository->reveal(),
            $uriTemplate->reveal(),
            $extraDataRepository->reveal()
        );

        $expectedCustomButtonSubmenuButtonView = [new SubmenuButtonView(
            'icon-Panier_2',
            'Mon bouton',
            'https://google.fr',
            false,
            null,
            false,
            ['target' => '_blank'],
            '#FF0000',
            '#FFFFFF',
            '#000000',
        )];

        $result = $customLinkSubmenuViewQueryHandler->handle(
            new CustomLinkSubmenuViewQuery(
                $sheet->reveal(),
                $user->reveal(),
                $event->reveal(),
                'fr'

            )
        );

        self::assertEquals($expectedCustomButtonSubmenuButtonView, $result);
    }

    public function testCustomButtonWithTechEventAndNotParticipantId(): void
    {
        $customLinkRepository = $this->prophesize(CustomLinkRepositoryInterface::class);
        $uriTemplate = $this->prophesize(UriTemplateInterface::class);
        $extraDataRepository = $this->prophesize(ExtraDataRepositoryInterface::class);
        $extraData = $this->prophesize(ExtraData::class);
        $participant = $this->prophesize(Participant::class);
        $participant->getId()->shouldBeCalled()->willReturn(null);
        $user = $this->prophesize(User::class);
        $user->getId()->shouldBeCalled()->willReturn(42);
        $user->getEmail()->willReturn('test@yahoo.fr');
        $event = $this->prophesize(Event::class);
        $type = $this->prophesize(Type::class);
        $sheet = $this->prophesize(Sheet::class);
        $sheet->getId()->shouldBeCalled()->willReturn(123456);
        $sheet->getType()->shouldBeCalled()->willReturn($type->reveal());
        $sheet->getUserParticipant($user->reveal())->shouldBeCalled()->willReturn($participant->reveal());
        $customLink = $this->prophesize(Event\CustomLink::class);
        $customLink->getUrl('fr')->shouldBeCalled()->willReturn('https://example.net/42/test@yahoo.fr/{participantId}/25f9e794323b453885f5181f1b624d0b/746/{techEventIdContact}');
        $customLink->getIconName()->shouldBeCalled()->willReturn('icon-Panier_2');
        $customLink->getLabel('fr')->shouldBeCalled()->willReturn('Mon bouton');
        $customLink->getIconColor()->shouldBeCalled()->willReturn('#FF0000');
        $customLink->getLabelColor()->shouldBeCalled()->willReturn('#FFFFFF');
        $customLink->getButtonColor()->shouldBeCalled()->willReturn('#000000');
        $extraDataRepository->getExtraDataForEventNameAndUser($event->reveal(),'tech_event_identifier_md5',$user->reveal())->shouldBeCalled()->willReturn($extraData->reveal());
        $extraData->getValue()->shouldBeCalled()->willReturn('tech_event_identifier_md5');
        $uriTemplate->render(
            'https://example.net/42/test@yahoo.fr/{participantId}/25f9e794323b453885f5181f1b624d0b/746/{techEventIdContact}',
            [
                'userId' => 42,
                'userEmail' => 'test%40yahoo.fr',
                'participantId' => null,
                'sheetId' => 123456,
                'techEventIdContact'=> 'tech_event_identifier_md5'
            ]
        )->shouldBeCalled()->willReturn('https://google.fr');

        $customLinkRepository->findByType($type->reveal())
            ->shouldBeCalled()
            ->willReturn([$customLink->reveal()]);

        $customLinkSubmenuViewQueryHandler = new CustomLinkSubmenuViewQueryHandler(
            $customLinkRepository->reveal(),
            $uriTemplate->reveal(),
            $extraDataRepository->reveal()
        );

        $expectedCustomButtonSubmenuButtonView = [new SubmenuButtonView(
            'icon-Panier_2',
            'Mon bouton',
            'https://google.fr',
            false,
            null,
            false,
            ['target' => '_blank'],
            '#FF0000',
            '#FFFFFF',
            '#000000',
        )];

        $result = $customLinkSubmenuViewQueryHandler->handle(
            new CustomLinkSubmenuViewQuery(
                $sheet->reveal(),
                $user->reveal(),
                $event->reveal(),
                'fr'

            )
        );

        self::assertEquals($expectedCustomButtonSubmenuButtonView, $result);
    }
}
