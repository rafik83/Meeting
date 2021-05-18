<?php

namespace Proximum\Vimeet\Tests\Application\Components\Transactional\Mail\Substitution;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution\EvaluatingSheetTitleSubstitution;
use Proximum\Vimeet\Application\Components\Transactional\Mail\View\PrepareMeetingFollowUpView;

class EvaluatingSheetTitleSubstitutionTest extends TestCase
{
    public function testSubstitute()
    {
        $mail = $this->prophesize(PrepareMeetingFollowUpView::class);
        $mail->reveal()->evaluatingSheetTitle = 'Fairness';

        $substitution = new EvaluatingSheetTitleSubstitution();
        $result = $substitution->substitute($mail->reveal());

        $this->assertEquals('Fairness', $result);
    }
}
