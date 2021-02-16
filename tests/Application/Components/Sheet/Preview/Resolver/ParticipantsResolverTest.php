<?php

namespace Proximum\Vimeet\Tests\Application\Components\Sheet\Preview\Resolver;

use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Components\Sheet\Preview\Resolver\ParticipantsResolver;
use Proximum\Vimeet\Application\Query\Participant\CardViewQuery;
use Proximum\Vimeet\Application\Query\Participant\CardViewQueryHandler;
use Proximum\Vimeet\Application\View\Participant\CardView;
use Proximum\Vimeet\Application\View\Sheet\Preview\PreviewView;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Rule\Applyer;
use Proximum\Vimeet\Domain\Template\AbstractChild;
use Proximum\Vimeet\Domain\Template\TemplateObject;

class ParticipantsResolverTest extends TestCase
{
    public function testHandle()
    {
        $locale = 'fr';
        $sheet  = $this->prophesize(Sheet::class);

        $participant1 = $this->prophesize(Participant::class);
        $participant2 = $this->prophesize(Participant::class);
        $sheet->getParticipants()->willReturn(new ArrayCollection([$participant1->reveal(), $participant2->reveal()]));

        $participantObject  = $this->prophesize(TemplateObject\Participant::class);
        $participantObject->getNumberOfParticipantShown()->willReturn(1);
        $participantObject->getKey()->willReturn('key_object_participants');
        $participantObject->getType()->willReturn(AbstractChild::TEMPLATE_OBJECT_TYPE_PARTICIPANT);

        $cardViewQueryHandler = $this->prophesize(CardViewQueryHandler::class);
        $cardViewQueryHandler
            ->handle(new CardViewQuery($participant1->reveal(), $locale))
            ->shouldBeCalled()
            ->willReturn(new CardView(1, false, 'Han', 'Solo', 'Pilot', 'hanSoloAvatar.jpg', false, 1))
        ;

        $applyer = $this->prophesize(Applyer::class);

        $participantResolver = new ParticipantsResolver($cardViewQueryHandler->reveal(), $applyer->reveal());
        $previewView = $participantResolver->handle($sheet->reveal(), $locale, $participantObject->reveal(), []);

        $expectedPreviewView = new PreviewView(
            'key_object_participants',
            '',
            'participant',
            [
                new CardView(
                    1,
                    false,
                    'Han',
                    'Solo',
                    'Pilot',
                    'hanSoloAvatar.jpg',
                    false,
                    1
                ),
            ]
        );

        $this->assertEquals($expectedPreviewView, $previewView);
    }
}
