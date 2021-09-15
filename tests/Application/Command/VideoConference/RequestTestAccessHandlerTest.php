<?php

namespace Proximum\Vimeet\Tests\Application\Command\VideoConference;

use OpenTok\Session;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\VideoConferenceAdapterInterface;
use Proximum\Vimeet\Application\Command\VideoConference\RequestTestAccess;
use Proximum\Vimeet\Application\Command\VideoConference\RequestTestAccessHandler;
use Proximum\Vimeet\Application\Components\Visio\VisioSettingsRetriever;
use Proximum\Vimeet\Application\View\Meeting\VideoConferenceView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Visio\VisioSettings;

class RequestTestAccessHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $date = new \DateTime();

        $videoConferenceAdapter = $this->prophesize(VideoConferenceAdapterInterface::class);
        $visioSettingsRetriever = $this->prophesize(VisioSettingsRetriever::class);

        $visioSettings = $this->prophesize(VisioSettings::class);
        $event = $this->prophesize(Event::class);
        $session = $this->prophesize(Session::class);
        $videoConferenceAdapter
            ->getSession('session_id')
            ->shouldBeCalled()
            ->willReturn($session->reveal())
        ;
        $visioSettingsRetriever
            ->get($event->reveal())
            ->shouldBeCalled()
            ->willReturn($visioSettings->reveal())
        ;

        $videoConferenceAdapter
            ->generateAccessToken(
                $session->reveal(),
                $date
            )->shouldBeCalled()
            ->willReturn('token')
        ;

        $session->getSessionId()->shouldBeCalled()->willReturn('session_id');
        $visioSettings->getHeader('fr')->shouldBeCalled()->willReturn('header.png');
        $visioSettings->getEndSound('fr')->shouldBeCalled()->willReturn('endsound.wav');
        $visioSettings->getEndImage('fr')->shouldBeCalled()->willReturn('endimage.png');
        $visioSettings->getEndMessage('fr')->shouldBeCalled()->willReturn('message');

        $videoConferenceAdapter->getApiKey()
            ->shouldBeCalled()
            ->willReturn('api_key')
        ;

        $handler = new RequestTestAccessHandler(
            $videoConferenceAdapter->reveal(),
            $visioSettingsRetriever->reveal(),
            $date
        );

        $result = $handler->handle(new RequestTestAccess($event->reveal(), 'session_id', 'fr'));

        $expected = new VideoConferenceView(
            'token',
            'session_id',
            'api_key',
            'header.png',
            'endsound.wav',
            'endimage.png',
            'message'
        );
        $this->assertEquals($expected, $result);
    }
}
