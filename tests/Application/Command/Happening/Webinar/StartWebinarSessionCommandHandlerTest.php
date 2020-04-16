<?php

namespace Proximum\Vimeet\Tests\Application\Command\Happening\Webinar;

use OpenTok\Session;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\VideoConferenceAdapterInterface;
use Proximum\Vimeet\Application\Command\Happening\Webinar\StartWebinarSessionCommand;
use Proximum\Vimeet\Application\Command\Happening\Webinar\StartWebinarSessionCommandHandler;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Repository\HappeningRepositoryInterface;

class StartWebinarSessionCommandHandlerTest extends TestCase
{
    /** @var ObjectProphecy|VideoConferenceAdapterInterface */
    private $videoConferenceAdapter;

    /** @var ObjectProphecy|HappeningRepositoryInterface */
    private $happeningRepository;

    /** @var StartWebinarSessionCommandHandler */
    private $startWebinarSessionCommandHandler;

    protected function setUp()
    {
        $this->videoConferenceAdapter = $this->prophesize(VideoConferenceAdapterInterface::class);
        $this->happeningRepository = $this->prophesize(HappeningRepositoryInterface::class);

        $this->startWebinarSessionCommandHandler = new StartWebinarSessionCommandHandler(
            $this->videoConferenceAdapter->reveal(), $this->happeningRepository->reveal()
        );
    }

    public function test_happening_have_session()
    {
        $happening = $this->prophesize(Happening::class);
        $happening->isWebinar()->shouldBeCalled()->willReturn(true);
        $happening->hasWebinarSessionId()->shouldBeCalled()->willReturn(true);

        $this->videoConferenceAdapter->createSession()->shouldNotBeCalled();
        $this->happeningRepository->set($happening)->shouldNotBeCalled();

        $this->startWebinarSessionCommandHandler->handle(new StartWebinarSessionCommand($happening->reveal()));
    }

    public function test_happening_have_not_session()
    {
        $happening = $this->prophesize(Happening::class);
        $happening->isWebinar()->shouldBeCalled()->willReturn(true);
        $happening->hasWebinarSessionId()->shouldBeCalled()->willReturn(false);
        $happening->setWebinarSessionId('webinar-session-id')->shouldBeCalled();

        $session = $this->prophesize(Session::class);
        $session->getSessionId()->shouldBeCalled()->willReturn('webinar-session-id');

        $this->videoConferenceAdapter->createSession()->shouldBeCalled()->willReturn($session->reveal());
        $this->happeningRepository->set($happening)->shouldBeCalled();

        $this->startWebinarSessionCommandHandler->handle(new StartWebinarSessionCommand($happening->reveal()));
    }
}
