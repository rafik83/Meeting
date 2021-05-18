<?php

namespace Proximum\Vimeet\Tests\Application\Components\Transactional\Mail\Substitution;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution\MeetingEvaluationSubstitution;
use Proximum\Vimeet\Application\Components\Transactional\Mail\View\PrepareMeetingFollowUpView;

class MeetingEvaluationSubstitutionTest extends TestCase
{
    public function testSubstitute()
    {
        $mail = $this->prophesize(PrepareMeetingFollowUpView::class);
        $mail->reveal()->evaluation = 3;

        $substitution = new MeetingEvaluationSubstitution();
        $result = $substitution->substitute($mail->reveal());

        $this->assertEquals('3', $result);
    }
}
