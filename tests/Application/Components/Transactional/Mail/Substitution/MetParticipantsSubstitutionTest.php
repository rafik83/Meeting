<?php

namespace Proximum\Vimeet\Tests\Application\Components\Transactional\Mail\Substitution;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution\MetParticipantsSubstitution;
use Proximum\Vimeet\Application\Components\Transactional\Mail\View\PrepareMeetingFollowUpView;
use Proximum\Vimeet\Application\View\Meeting\FollowUpParticipantListView;
use Proximum\Vimeet\Application\View\Meeting\FollowUpParticipantView;
use Proximum\Vimeet\Domain\Adapter\TemplatingAdapterInterface;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Meeting\MeetingFollowUpMail;

class MetParticipantsSubstitutionTest extends TestCase
{
    public function testSubstitute()
    {
        $participantViews = [$this->prophesize(FollowUpParticipantView::class)->reveal()];

        $sheet = $this->prophesize(Sheet::class);
        $sheet->getId()->willReturn(123);

        $prepareMailView = $this->prophesize(PrepareMeetingFollowUpView::class);
        $prepareMailView->reveal()->sheet = $sheet->reveal();
        $prepareMailView->reveal()->metParticipants = new FollowUpParticipantListView($participantViews);
        $prepareMailView->reveal()->showEmail = true;
        $prepareMailView->reveal()->showPhone = false;

        $templating = $this->prophesize(TemplatingAdapterInterface::class);
        $templating->render(
                MeetingFollowUpMail::TEMPLATE_MET_PARTICIPANTS,
                [
                    'metParticipantViews' => $participantViews,
                    'evaluatedSheetId' => 123,
                    'showEmail' => true,
                    'showPhone' => false,
                ]
            )
            ->shouldBeCalled()
            ->willReturn('<table>list...</table>');

        $substitution = new MetParticipantsSubstitution($templating->reveal());
        $result = $substitution->substitute($prepareMailView->reveal());

        $this->assertEquals('<table>list...</table>', $result);
    }
}
