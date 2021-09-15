<?php

namespace Proximum\Vimeet\Tests\Application\Command\Happening\Webinar\Poll;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\NotificationPublisherInterface;
use Proximum\Vimeet\Application\Command\Happening\Webinar\Poll\PollHappeningMismatchException;
use Proximum\Vimeet\Application\Command\Happening\Webinar\Poll\Update;
use Proximum\Vimeet\Application\Command\Happening\Webinar\Poll\UpdateHandler;
use Proximum\Vimeet\Application\Exception\Happening\PollNotFoundException;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Happening\Poll;
use Proximum\Vimeet\Domain\Repository\Happening\PollRepositoryInterface;

class UpdateHandlerTest extends TestCase
{
    private ObjectProphecy $pollRepository;
    private ObjectProphecy $notificationPublisher;
    private UpdateHandler $updateHandler;

    protected function setUp(): void
    {
        $this->pollRepository = $this->prophesize(PollRepositoryInterface::class);
        $this->notificationPublisher = $this->prophesize(NotificationPublisherInterface::class);

        $this->updateHandler = new UpdateHandler($this->pollRepository->reveal(), $this->notificationPublisher->reveal());
    }

    public function testHandleWithoutPublish()
    {
        $choices = [
            ['content' => 'Good'],
            ['content' => 'Bad'],
        ];

        $poll = $this->prophesize(Poll::class);
        $poll->getHappening()->willReturn($this->createHappening()->reveal());

        $this->pollRepository->findById(42)->willReturn($poll->reveal());

        $this->updateHandler->handle(new Update(
            42,
            21,
            'How do you feel ?',
            $choices,
            true,
            false
        ));

        $poll->update('How do you feel ?', $choices, true)->shouldHaveBeenCalled();
        $this->pollRepository->update($poll->reveal())->shouldHaveBeenCalled();
        $this->notificationPublisher->publishNewPublishedPollNotification()->shouldNotHaveBeenCalled();
    }

    public function testHandleWithPublish()
    {
        $choices = [
            ['content' => 'Good'],
            ['content' => 'Bad'],
        ];

        $poll = $this->prophesize(Poll::class);
        $poll->getHappening()->willReturn($this->createHappening()->reveal());

        $this->pollRepository->findById(42)->willReturn($poll->reveal());

        $this->updateHandler->handle(new Update(
            42,
            21,
            'How do you feel ?',
            $choices,
            true,
            true
        ));

        $poll->setPublished()->shouldHaveBeenCalled();
        $poll->update('How do you feel ?', $choices, true)->shouldHaveBeenCalled();
        $this->pollRepository->update($poll->reveal())->shouldHaveBeenCalled();
        $this->notificationPublisher->publishNewPublishedPollNotification($poll->reveal())->shouldHaveBeenCalled();
    }

    public function testPollNotFound()
    {
        $this->expectException(PollNotFoundException::class);

        $choices = [
            ['content' => 'Good'],
            ['content' => 'Bad'],
        ];

        $this->pollRepository->findById(43)->willReturn(null);

        $this->updateHandler->handle(new Update(
            43,
            21,
            'How do you feel ?',
            $choices,
            true,
            false
        ));
    }

    public function testHappeningIncorrect()
    {
        $this->expectException(PollHappeningMismatchException::class);

        $choices = [
            ['content' => 'Good'],
            ['content' => 'Bad'],
        ];

        $poll = $this->prophesize(Poll::class);
        $poll->getHappening()->willReturn($this->createHappening()->reveal());
        $this->pollRepository->findById(42)->willReturn($poll->reveal());

        $this->updateHandler->handle(new Update(
            42,
            22,
            'How do you feel ?',
            $choices,
            true,
            false
        ));
    }

    private function createHappening($id = 21): ObjectProphecy
    {
        $happening = $this->prophesize(Happening::class);
        $happening->getId()->willReturn($id);

        return $happening;
    }
}
