<?php

namespace Proximum\Vimeet\Tests\Application\Query\Happening\Webinar;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Query\Happening\Webinar\CanAccessToWebinar;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\HappeningParticipation;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\HappeningParticipationRepositoryInterface;

class CanAccessToWebinarTest extends TestCase
{
    /** @var ObjectProphecy|HappeningParticipationRepositoryInterface */
    private $happeningParticipationRepository;

    /** @var \DateTime */
    private $datetime;

    /** @var CanAccessToWebinar */
    private $canAccessToWebinar;

    /** @var ObjectProphecy|Happening */
    private $happening;

    /** @var ObjectProphecy|User */
    private $user;

    protected function setUp()
    {
        $this->happening = $this->prophesize(Happening::class);
        $this->user = $this->prophesize(User::class);

        $this->datetime = new \DateTime('2020-04-12 09:33:00');
        $this->happeningParticipationRepository = $this->prophesize(HappeningParticipationRepositoryInterface::class);
        $this->canAccessToWebinar = new CanAccessToWebinar(
            $this->happeningParticipationRepository->reveal(),
            $this->datetime,
            true,
            true
        );
    }

    public function test_happening_not_a_webinar()
    {
        $this->happening->isWebinar()->shouldBeCalled()->willReturn(false);
        $this->assertFalse(
            $this->canAccessToWebinar->isSatisfiableBy($this->happening->reveal(), $this->user->reveal())
        );
    }

    public function test_webinar_not_opened()
    {
        $this->happening->isWebinar()->shouldBeCalled()->willReturn(true);
        $this->happening->hasSpeaker($this->user->reveal())->shouldBeCalled()->willReturn(false);
        $this->happening->getBegin()->shouldBeCalled()->willReturn(new \DateTime('2020-04-12 09:45:00'));
        $this->happening->getEnd()->shouldBeCalled()->willReturn(new \DateTime('2020-04-12 10:15:00'));

        $this->assertFalse(
            $this->canAccessToWebinar->isSatisfiableBy($this->happening->reveal(), $this->user->reveal())
        );
    }

    public function test_user_is_webinar_speaker()
    {
        $this->happening->isWebinar()->shouldBeCalled()->willReturn(true);
        $this->happening->getBegin()->shouldBeCalled()->willReturn(new \DateTime('2020-04-12 09:35:00'));
        $this->happening->getEnd()->shouldBeCalled()->willReturn(new \DateTime('2020-04-12 10:00:00'));
        $this->happening->hasSpeaker($this->user->reveal())->shouldBeCalled()->willReturn(true);

        $this->assertTrue(
            $this->canAccessToWebinar->isSatisfiableBy($this->happening->reveal(), $this->user->reveal())
        );
    }

    public function test_user_is_webinar_participant()
    {
        $this->happening->isWebinar()->shouldBeCalled()->willReturn(true);
        $this->happening->getBegin()->shouldBeCalled()->willReturn(new \DateTime('2020-04-12 09:20:00'));
        $this->happening->getEnd()->shouldBeCalled()->willReturn(new \DateTime('2020-04-12 09:45:00'));
        $this->happening->hasSpeaker($this->user->reveal())->shouldBeCalled()->willReturn(false);

        $happeningParticipation = $this->prophesize(HappeningParticipation::class);
        $this->happeningParticipationRepository
            ->findByHappeningAndUser($this->happening->reveal(), $this->user->reveal())
            ->shouldBeCalled()
            ->willReturn($happeningParticipation->reveal());

        $this->assertTrue(
            $this->canAccessToWebinar->isSatisfiableBy($this->happening->reveal(), $this->user->reveal())
        );
    }

    public function test_user_is_not_webinar_participant()
    {
        $this->happening->isWebinar()->shouldBeCalled()->willReturn(true);
        $this->happening->getBegin()->shouldBeCalled()->willReturn(new \DateTime('2020-04-12 09:20:00'));
        $this->happening->getEnd()->shouldBeCalled()->willReturn(new \DateTime('2020-04-12 09:45:00'));
        $this->happening->hasSpeaker($this->user->reveal())->shouldBeCalled()->willReturn(false);

        $this->happeningParticipationRepository
            ->findByHappeningAndUser($this->happening->reveal(), $this->user->reveal())
            ->shouldBeCalled()
            ->willReturn(null);

        $this->assertFalse(
            $this->canAccessToWebinar->isSatisfiableBy($this->happening->reveal(), $this->user->reveal())
        );
    }
}
