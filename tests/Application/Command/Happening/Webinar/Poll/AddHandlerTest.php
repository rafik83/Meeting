<?php

namespace Proximum\Vimeet\Tests\Application\Command\Happening\Webinar\Poll;

use DateTimeInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\NotificationPublisherInterface;
use Proximum\Vimeet\Application\Command\Happening\Webinar\Poll\Add;
use Proximum\Vimeet\Application\Command\Happening\Webinar\Poll\AddHandler;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Happening\Poll;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Happening\PollRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\UserFactory;

class AddHandlerTest extends TestCase
{
    private ObjectProphecy $pollRepository;
    private DateTimeInterface $dateTime;
    private ObjectProphecy $notificationPublisher;
    private ObjectProphecy $happening;
    private User $user;
    private AddHandler $addHandler;

    protected function setUp(): void
    {
        $this->pollRepository = $this->prophesize(PollRepositoryInterface::class);
        $this->dateTime = \DateTime::createFromFormat('!Y-m-d H:i', '2020-11-09 02:00');
        $this->notificationPublisher = $this->prophesize(NotificationPublisherInterface::class);

        $this->happening = $this->prophesize(Happening::class);
        $this->user = UserFactory::create();

        $this->addHandler = new AddHandler(
            $this->pollRepository->reveal(),
            $this->dateTime,
            $this->notificationPublisher->reveal()
        );
    }

    public function testHandleAddDrafTPoll()
    {

        $choices = [
            ['id' => 11, 'content' => 'Good'],
            ['id' => 12, 'content' => 'Bad'],
        ];

        $this->addHandler->handle(new Add(
            $this->happening->reveal(),
            $this->user,
            'How do you feel ?',
            $choices,
            true,
            false
        ));

        $expectedPoll = new Poll(
            $this->happening->reveal(),
            $this->user,
            $this->dateTime,
            'How do you feel ?',
            $choices,
            true
        );

        $this->pollRepository->add($expectedPoll)->shouldHaveBeenCalled();

        $this->assertEquals(Poll::STATUS_DRAFT, $expectedPoll->getStatus());
    }

    public function testHandleAddPublishedPoll()
    {

        $choices = [
            ['id' => 11, 'content' => 'Good'],
            ['id' => 12, 'content' => 'Bad'],
        ];

        $this->addHandler->handle(new Add(
            $this->happening->reveal(),
            $this->user,
            'How do you feel ?',
            $choices,
            true,
            true
        ));

        $this->pollRepository
            ->add(Argument::that(fn (Poll $expectedPoll) => $expectedPoll->isPublished()))
            ->shouldHaveBeenCalled();

        $this->notificationPublisher
            ->publishNewPublishedPollNotification(Argument::type(Poll::class))
            ->shouldHaveBeenCalled();
    }
}
