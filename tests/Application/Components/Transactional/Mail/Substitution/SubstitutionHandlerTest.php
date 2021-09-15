<?php

namespace Proximum\Vimeet\Tests\Application\Components\Transactional\Mail\Substitution;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution\EventSubstitution;
use Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution\SubstitutionHandler;
use Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution\SubstitutionResult;
use Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution\SubstitutionsProviders;
use Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution\UserFirstNameSubstitution;
use Proximum\Vimeet\Application\Components\Transactional\Mail\View\PrepareUserRegisteredMailView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Transactional\Mail\Message;
use Proximum\Vimeet\Domain\Model\User;

class SubstitutionHandlerTest extends TestCase
{
    public function testHandle()
    {
        $message = $this->prophesize(Message::class);
        $user = $this->prophesize(User::class);
        $event = $this->prophesize(Event::class);
        $event->getAvailableLocale('fr')->shouldBeCalled()->willReturn('en');
        $message->getSubject('en')->shouldBeCalled()->willReturn('Hello %firstName%, this is the subject');
        $message->getContent('en')->shouldBeCalled()->willReturn('Hello %firstName%,\n This is the content for the event %event%');

        $mail = new PrepareUserRegisteredMailView(
            $event->reveal(),
            $user->reveal(),
            'fr'
        );

        $substitutionProviders = $this->prophesize(SubstitutionsProviders::class);
        $firstNameSubstitution = $this->prophesize(UserFirstNameSubstitution::class);
        $eventSubstitution = $this->prophesize(EventSubstitution::class);
        $firstNameSubstitution->substitute($mail)->shouldBeCalled()->willReturn('Jean');
        $eventSubstitution->substitute($mail)->shouldBeCalled()->willReturn('Super Event');

        $substitutionProviders->getSubstitution('%firstName%')->shouldBeCalled()->willReturn($firstNameSubstitution->reveal());
        $substitutionProviders->getSubstitution('%event%')->shouldBeCalled()->willReturn($eventSubstitution->reveal());

        $substitutionHandler = new SubstitutionHandler($substitutionProviders->reveal());
        $result = $substitutionHandler->handle($mail, $message->reveal());

        $expected = new SubstitutionResult(
            'Hello Jean, this is the subject',
            'Hello Jean,\n This is the content for the event Super Event',
            [
                '%firstName%' => 'Jean',
            ],
            [
                '%firstName%' => 'Jean',
                '%event%' => 'Super Event',
            ]
        );

        $this->assertEquals($expected, $result);
    }
}
