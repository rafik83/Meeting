<?php

namespace Proximum\Vimeet\Tests\Application\Components\Transactional\Mail\Substitution;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution\EventSubstitution;
use Proximum\Vimeet\Application\Components\Transactional\Mail\View\PrepareUserRegisteredMailView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;

class EventSubstitutionTest extends TestCase
{
    public function testSubstitute()
    {
        $event = $this->prophesize(Event::class);
        $event->getTitle()->shouldBeCalled()->willReturn('Event title');
        $user = $this->prophesize(User::class);

        $mail = new PrepareUserRegisteredMailView($event->reveal(), $user->reveal(), 'fr');

        $substitution = new EventSubstitution();
        $result = $substitution->substitute($mail);

        $this->assertEquals('Event title', $result);
    }
}
