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

        $mail = $this->prophesize(PrepareMeetingFollowUpView::class);
        $mail->reveal()->sheet = $sheet->reveal();
        $mail->reveal()->metParticipants = new FollowUpParticipantListView($participantViews);

        $templating = $this->prophesize(TemplatingAdapterInterface::class);
        $templating->render(
                MeetingFollowUpMail::TEMPLATE_MET_PARTICIPANTS,
                [
                    'metParticipantViews' => $participantViews,
                    'evaluatedSheetId' => 123,
                ]
            )
            ->shouldBeCalled()
            ->willReturn('<table>list...</table>');

        $substitution = new MetParticipantsSubstitution($templating->reveal());
        $result = $substitution->substitute($mail->reveal());

        $this->assertEquals('<table>list...</table>', $result);
    }
}
