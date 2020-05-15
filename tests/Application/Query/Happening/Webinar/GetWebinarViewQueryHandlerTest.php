<?php

namespace Proximum\Vimeet\Tests\Application\Query\Happening\Webinar;

use OpenTok\Session;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\VideoConferenceAdapterInterface;
use Proximum\Vimeet\Application\Query\Happening\Webinar\GetWebinarViewQuery;
use Proximum\Vimeet\Application\Query\Happening\Webinar\GetWebinarViewQueryHandler;
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

    protected function setUp()
    {
        $this->videoConferenceAdapter = $this->prophesize(VideoConferenceAdapterInterface::class);
        $this->dateTime = new \DateTime('2020-03-30 12:00:00');

        $this->getWebinarViewQueryHandler = new GetWebinarViewQueryHandler(
            $this->videoConferenceAdapter->reveal(),
            $this->dateTime
        );
    }

    public function testHandle()
    {
        $user = $this->prophesize(User::class);

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

        $this->assertEquals(
            new WebinarView(
                'Webinar: how to work remotely during the Covid-19 crisis',
                'User token',
                'webinar-session-id',
                'api key',
                true,
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
