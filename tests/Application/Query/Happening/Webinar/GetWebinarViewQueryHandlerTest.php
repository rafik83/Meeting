<?php

namespace Proximum\Vimeet\Tests\Application\Query\Happening\Webinar;

use OpenTok\Session;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\VideoConferenceAdapterInterface;
use Proximum\Vimeet\Application\Query\Happening\Webinar\GetWebinarViewQuery;
use Proximum\Vimeet\Application\Query\Happening\Webinar\GetWebinarViewQueryHandler;
use Proximum\Vimeet\Application\View\Happening\WebinarSpeakerView;
use Proximum\Vimeet\Application\View\Happening\WebinarView;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Time\TimeRangeView;

class GetWebinarViewQueryHandlerTest extends TestCase
{
    /** @var ObjectProphecy|VideoConferenceAdapterInterface */
    private $videoConferenceAdapter;

    /** @var GetWebinarViewQueryHandler */
    private $getWebinarViewQueryHandler;

    /** @var \DateTime */
    private $dateTime;

    protected function setUp(): void
    {
        $this->videoConferenceAdapter = $this->prophesize(VideoConferenceAdapterInterface::class);
        $this->dateTime = new \DateTime('2020-03-30 12:00:00');

        $this->getWebinarViewQueryHandler = new GetWebinarViewQueryHandler(
            $this->videoConferenceAdapter->reveal(),
            $this->dateTime
        );
    }

    public function testHandle(): void
    {
        $user = $this->prophesize(User::class);
        $user->getId()->shouldBeCalled()->willReturn(111);

        $user1 = $this->prophesize(User::class);
        $user2 = $this->prophesize(User::class);
        $speaker1 = $this->prophesize(Happening\Speaker::class);
        $speaker2 = $this->prophesize(Happening\Speaker::class);
        $speaker1->getUser()->willReturn($user1->reveal());
        $speaker2->getUser()->willReturn($user2->reveal());
        $user1->getId()->willReturn(1);
        $user2->getId()->willReturn(2);
        $speaker1->getFirstname()->willReturn('Jeanne');
        $speaker2->getFirstname()->willReturn('John');
        $speaker1->getLastname()->willReturn('Dupont');
        $speaker2->getLastname()->willReturn('Doe');
        $speaker1->getPosition('en')->willReturn('Développeuse');
        $speaker2->getPosition('en')->willReturn('Ingénieur');
        $speaker1->getOrganization()->willReturn('Fairness');
        $speaker2->getOrganization()->willReturn('Proximum');

        $happening = $this->prophesize(Happening::class);
        $happening->getTitle('en')->shouldBeCalled()->willReturn(
            'Webinar: how to work remotely during the Covid-19 crisis'
        );
        $happening->hasWebinarSessionId()->shouldBeCalled()->willReturn(true);
        $happening->getWebinarSessionId()->shouldBeCalled()->willReturn('webinar-session-id');
        $happening->hasSpeaker($user->reveal())->shouldBeCalled()->willReturn(true);
        $happening->getBegin()->shouldBeCalled()->willReturn(new \DateTime('2020-03-30 11:55:00'));
        $happening->getEnd()->shouldBeCalled()->willReturn(new \DateTime('2020-03-30 12:15:00'));
        $happening->getWebinarHeaderImage('en')->shouldBeCalled()->willReturn('/path/image.jpg');

        $session = $this->prophesize(Session::class);
        $session->getSessionId()->shouldBeCalled()->willReturn('webinar-session-id');

        $this->videoConferenceAdapter->getSession('webinar-session-id')->shouldBeCalled()->willReturn(
            $session->reveal()
        );
        $this->videoConferenceAdapter->getApiKey()->shouldBeCalled()->willReturn('api key');

        $this->videoConferenceAdapter->generateAccessToken(
            $session->reveal(),
            new \DateTime('2020-03-30 12:15:00'),
            [],
            true
        )->shouldBeCalled()->willReturn('User token');

        $speakers = [
            new WebinarSpeakerView(
                1,
                'Jeanne',
                'Dupont',
                'Développeuse',
                'Fairness'
            ),
            new WebinarSpeakerView(
                2,
                'John',
                'Doe',
                'Ingénieur',
                'Proximum'
            ),
        ];

        $this->assertEquals(
            new WebinarView(
                111,
                'Webinar: how to work remotely during the Covid-19 crisis',
                'User token',
                'webinar-session-id',
                'api key',
                true,
                $speakers,
                new TimeRangeView(new \DateTime('2020-03-30 11:55:00'), new \DateTime('2020-03-30 12:15:00')),
                $this->dateTime,
                900,
                180,
                '/path/image.jpg'
            ),
            $this->getWebinarViewQueryHandler->handle(new GetWebinarViewQuery($happening->reveal(), $user->reveal(), 'en'))
        );
    }
}
