<?php

namespace Proximum\Vimeet\Tests\Application\Components\Transactional\Mail\Substitution;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution\FromTypeTitleSubstitution;
use Proximum\Vimeet\Application\Components\Transactional\Mail\View\PrepareSheetChangeTypeMailView;
use Proximum\Vimeet\Application\Components\Transactional\Mail\View\PrepareUserCompleteProfileMailView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class FromTypeTitleSubstitutionTest extends TestCase
{
    public function testSubstituteWithoutFromType(): void
    {
        $prepareMail = $this->prophesize(PrepareUserCompleteProfileMailView::class);

        $typeTitleSubstitution = new FromTypeTitleSubstitution();

        $expected = '';
        $result = $typeTitleSubstitution->substitute($prepareMail->reveal());

        $this->assertEquals($expected, $result);
    }

    public function testSubstituteWithFromType(): void
    {
        $locale = 'fr';

        $event = $this->prophesize(Event::class);
        $user = $this->prophesize(User::class);
        $sheet = $this->prophesize(Sheet::class);

        $prepareMail = new PrepareSheetChangeTypeMailView(
            $event->reveal(),
            $sheet->reveal(),
            $user->reveal(),
            $locale,
            'from type title'
        );

        $typeTitleSubstitution = new FromTypeTitleSubstitution();

        $expected = 'from type title';
        $result = $typeTitleSubstitution->substitute($prepareMail);

        $this->assertEquals($expected, $result);
    }
}
