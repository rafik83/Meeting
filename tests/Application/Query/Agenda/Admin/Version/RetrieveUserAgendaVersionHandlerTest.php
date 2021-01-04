<?php

namespace Proximum\Vimeet\Tests\Application\Query\Agenda\Admin\Version;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Query\Agenda\Admin\Version\RetrieveUserAgendaVersion;
use Proximum\Vimeet\Application\Query\Agenda\Admin\Version\RetrieveUserAgendaVersionHandler;
use Proximum\Vimeet\Application\View\Agenda\Admin\Version\UserAgendaVersionDiffView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\User\Agenda\Version;
use Proximum\Vimeet\Domain\Model\User\UserEventPhone;
use Proximum\Vimeet\Domain\Repository\User\Agenda\VersionRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\User\UserEventPhoneRepositoryInterface;
use Proximum\Vimeet\Domain\User\Agenda\Version\DiffChecker;
use Proximum\Vimeet\Domain\User\Agenda\Version\DiffVerbalizer;
use Proximum\Vimeet\Domain\User\Agenda\Version\Generator;

class RetrieveUserAgendaVersionHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $user;

    /** @var ObjectProphecy */
    private $event;

    /** @var ObjectProphecy */
    private $userEventPhoneRepository;

    /** @var ObjectProphecy */
    private $versionRepository;

    /** @var ObjectProphecy */
    private $diffChecker;

    /** @var ObjectProphecy */
    private $generator;

    /** @var ObjectProphecy */
    private $diffVerbalizer;

    /** @var ObjectProphecy */
    private $dateTime;

    public function setUp()
    {
        $this->userEventPhoneRepository = $this->prophesize(UserEventPhoneRepositoryInterface::class);
        $this->versionRepository = $this->prophesize(VersionRepositoryInterface::class);
        $this->diffChecker = $this->prophesize(DiffChecker::class);
        $this->generator = $this->prophesize(Generator::class);
        $this->diffVerbalizer = $this->prophesize(DiffVerbalizer::class);
        $this->dateTime = new \DateTime();
        $this->event = $this->prophesize(Event::class);
        $this->user = $this->prophesize(User::class);
    }

    public function testNoPhone()
    {
        // Expected
        $this->userEventPhoneRepository
            ->findValidated($this->user->reveal(), $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(null);

        // Handler
        $queryHandler = new RetrieveUserAgendaVersionHandler(
            $this->userEventPhoneRepository->reveal(),
            $this->versionRepository->reveal(),
            $this->dateTime,
            $this->diffChecker->reveal(),
            $this->generator->reveal(),
            $this->diffVerbalizer->reveal()
        );
        $result = $queryHandler->handle(new RetrieveUserAgendaVersion($this->event->reveal(), $this->user->reveal()));

        $this->assertEquals(
            new UserAgendaVersionDiffView(UserAgendaVersionDiffView::ANSWER_NO_PHONE),
            $result
        );
    }

    public function testNoDiffNoOldVersion()
    {
        $phone = $this->prophesize(UserEventPhone::class);

        // Expected
        $this->userEventPhoneRepository
            ->findValidated($this->user->reveal(), $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn($phone->reveal());

        $this->versionRepository
            ->getLastVersionByEventAndUser($this->event->reveal(), $this->user->reveal())
            ->shouldBeCalled()
            ->willReturn(null);

        $this->generator
            ->generate($this->event->reveal(), $this->user->reveal())
            ->shouldBeCalled()
            ->willReturn([])
        ;

        $this->diffChecker
            ->hasDiff(
                new Version($this->event->reveal(), $this->user->reveal(), [], $this->dateTime),
                []
            )->shouldBeCalled()
            ->willReturn(false);

        $queryHandler = new RetrieveUserAgendaVersionHandler(
            $this->userEventPhoneRepository->reveal(),
            $this->versionRepository->reveal(),
            $this->dateTime,
            $this->diffChecker->reveal(),
            $this->generator->reveal(),
            $this->diffVerbalizer->reveal()
        );
        $result = $queryHandler->handle(new RetrieveUserAgendaVersion($this->event->reveal(), $this->user->reveal()));

        $this->assertEquals(
            new UserAgendaVersionDiffView(UserAgendaVersionDiffView::ANSWER_NO_DIFF),
            $result
        );
    }

    public function testNoDiff()
    {
        $phone = $this->prophesize(UserEventPhone::class);
        $version = new Version($this->event->reveal(), $this->user->reveal(), ['version'], $this->dateTime);

        // Expected
        $this->userEventPhoneRepository
            ->findValidated($this->user->reveal(), $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn($phone->reveal());

        $this->versionRepository
            ->getLastVersionByEventAndUser($this->event->reveal(), $this->user->reveal())
            ->shouldBeCalled()
            ->willReturn($version);

        $this->generator
            ->generate($this->event->reveal(), $this->user->reveal())
            ->shouldBeCalled()
            ->willReturn(['version'])
        ;

        $this->diffChecker
            ->hasDiff(
                $version,
                ['version']
            )->shouldBeCalled()
            ->willReturn(false);

        $queryHandler = new RetrieveUserAgendaVersionHandler(
            $this->userEventPhoneRepository->reveal(),
            $this->versionRepository->reveal(),
            $this->dateTime,
            $this->diffChecker->reveal(),
            $this->generator->reveal(),
            $this->diffVerbalizer->reveal()
        );
        $result = $queryHandler->handle(new RetrieveUserAgendaVersion($this->event->reveal(), $this->user->reveal()));

        $this->assertEquals(
            new UserAgendaVersionDiffView(UserAgendaVersionDiffView::ANSWER_NO_DIFF),
            $result
        );
    }

    public function testDiff()
    {
        $this->user->getLocale()->willReturn('fr');
        $phone = $this->prophesize(UserEventPhone::class);
        $version = new Version($this->event->reveal(), $this->user->reveal(), ['version'], $this->dateTime);

        // Expected
        $this->userEventPhoneRepository
            ->findValidated($this->user->reveal(), $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn($phone->reveal());

        $this->versionRepository
            ->getLastVersionByEventAndUser($this->event->reveal(), $this->user->reveal())
            ->shouldBeCalled()
            ->willReturn($version);

        $this->generator
            ->generate($this->event->reveal(), $this->user->reveal())
            ->shouldBeCalled()
            ->willReturn(['new version'])
        ;

        $this->diffChecker
            ->hasDiff(
                $version,
                ['new version']
            )->shouldBeCalled()
            ->willReturn(true);

        $this->diffVerbalizer
            ->verbalizeDiff($version, ['new version'], 'fr')
            ->shouldBeCalled()
            ->willReturn("Toto is in the Kitchen\nI repeat, Toto is in the kitchen is the diff");

        $queryHandler = new RetrieveUserAgendaVersionHandler(
            $this->userEventPhoneRepository->reveal(),
            $this->versionRepository->reveal(),
            $this->dateTime,
            $this->diffChecker->reveal(),
            $this->generator->reveal(),
            $this->diffVerbalizer->reveal()
        );
        $result = $queryHandler->handle(new RetrieveUserAgendaVersion($this->event->reveal(), $this->user->reveal()));

        $this->assertEquals(
            new UserAgendaVersionDiffView(
                UserAgendaVersionDiffView::ANSWER_DIFF,
                "Toto is in the Kitchen\nI repeat, Toto is in the kitchen is the diff"
            ),
            $result
        );
    }
}
