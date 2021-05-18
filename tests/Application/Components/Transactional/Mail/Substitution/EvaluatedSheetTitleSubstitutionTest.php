<?php

namespace Proximum\Vimeet\Tests\Application\Components\Transactional\Mail\Substitution;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution\EvaluatedSheetTitleSubstitution;
use Proximum\Vimeet\Application\Components\Transactional\Mail\View\PrepareMeetingFollowUpView;
use Proximum\Vimeet\Domain\Model\Sheet;

class EvaluatedSheetTitleSubstitutionTest extends TestCase
{
    public function testSubstitute()
    {
        $sheet = $this->prophesize(Sheet::class);
        $sheet->getTitle()->shouldBeCalled()->willReturn('Acme Corp');

        $mail = $this->prophesize(PrepareMeetingFollowUpView::class);
        $mail->reveal()->sheet = $sheet->reveal();

        $substitution = new EvaluatedSheetTitleSubstitution();
        $result = $substitution->substitute($mail->reveal());

        $this->assertEquals('Acme Corp', $result);
    }
}
