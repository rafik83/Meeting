<?php

namespace Proximum\Vimeet\Tests\Application\Query\Happening\Webinar\Poll;

use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Query\Happening\Webinar\Poll\GetHappeningPoll;
use Proximum\Vimeet\Application\Query\Happening\Webinar\Poll\GetHappeningPollHandler;
use Proximum\Vimeet\Application\Query\Happening\Webinar\Poll\GetHappeningPolls;
use Proximum\Vimeet\Application\Query\Happening\Webinar\Poll\GetHappeningPollsHandler;
use Proximum\Vimeet\Application\Query\Happening\Webinar\Poll\PollChoiceView;
use Proximum\Vimeet\Application\Query\Happening\Webinar\Poll\PollView;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Happening\Poll;
use Proximum\Vimeet\Domain\Model\Happening\PollChoice;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Poll\CanUserVoteOnPoll;
use Proximum\Vimeet\Domain\Repository\Happening\PollRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\UserFactory;

class GetHappeningPollsHandlerTest extends TestCase
{
    private ObjectProphecy $happening;
    private User $pollAuthor;
    private DateTimeInterface $dateTime;
    private GetHappeningPollsHandler $getHappeningPollsHandler;
    private ObjectProphecy $pollRepository;
    private ObjectProphecy $canUserVoteOnPoll;
    /**
     * @var GetHappeningPollHandler|ObjectProphecy
     */
    private $getHappeningPollHandler;

    protected function setUp()
    {
        $this->happening = $this->prophesize(Happening::class);
        $this->pollAuthor = UserFactory::create();
        $this->dateTime = DateTime::createFromFormat('!Y-m-d H:i', '2020-03-14 15:09');

        $this->pollRepository = $this->prophesize(PollRepositoryInterface::class);
        $this->canUserVoteOnPoll = $this->prophesize(CanUserVoteOnPoll::class);

        $this->getHappeningPollHandler = $this->prophesize(GetHappeningPollHandler::class);

        $this->getHappeningPollsHandler = new GetHappeningPollsHandler(
            $this->pollRepository->reveal(),
            $this->canUserVoteOnPoll->reveal(),
            $this->getHappeningPollHandler->reveal()
        );
    }

    public function testHandleCantVoteViewer(): void
    {
        $pollChoiceView = [new PollChoiceView(11, 'Option #1', 30), new PollChoiceView(12, 'Option #2', 70)];
        $pollView = new PollView(1, 'How do you feel today?', $pollChoiceView, false, Poll::STATUS_DRAFT, 0, false, '1234567', false);

        $poll = $this->createPoll();
        $this->pollRepository->findByHappening($this->happening->reveal(), null)->shouldBeCalled()->willReturn([$poll]);
        $this->happening->hasSpeaker($this->pollAuthor)->willReturn(false);
        $this->canUserVoteOnPoll->isSatisfiedBy($poll, $this->pollAuthor)->willReturn(false);
        $this->getHappeningPollHandler->handle(new GetHappeningPoll($poll, true))->willReturn($pollView);

        $expected = [$pollView];

        $result = $this->getHappeningPollsHandler->handle(
            new GetHappeningPolls($this->happening->reveal(),
                $this->pollAuthor,
                'fr',
                null
            )
        );

        self::assertEquals($expected, $result);
    }

    public function testHandleCanVoteViewer(): void
    {
        $pollChoiceView = [new PollChoiceView(11, 'Option #1', null), new PollChoiceView(12, 'Option #2', null)];
        $pollView = new PollView(1, 'How do you feel today?', $pollChoiceView, false, Poll::STATUS_DRAFT, 0, false, '1234567', false);

        $poll = $this->createPoll();
        $this->pollRepository->findByHappening($this->happening->reveal(), null)->shouldBeCalled()->willReturn([$poll]);
        $this->happening->hasSpeaker($this->pollAuthor)->willReturn(false);
        $this->canUserVoteOnPoll->isSatisfiedBy($poll, $this->pollAuthor)->willReturn(true);
        $this->getHappeningPollHandler->handle(new GetHappeningPoll($poll, false))->willReturn($pollView);

        $expected = [$pollView];

        $result = $this->getHappeningPollsHandler->handle(
            new GetHappeningPolls($this->happening->reveal(),
                                  $this->pollAuthor,
                                  'fr',
                                  null
            )
        );

        self::assertEquals($expected, $result);
    }

    public function testHandleSpeaker(): void
    {
        $pollChoiceView = [new PollChoiceView(11, 'Option #1', 30), new PollChoiceView(12, 'Option #2', 70)];
        $pollView = new PollView(1, 'How do you feel today?', $pollChoiceView, false, Poll::STATUS_DRAFT, 0, false, '1234567', false);

        $poll = $this->createPoll();
        $this->pollRepository->findByHappening($this->happening->reveal(), null)->shouldBeCalled()->willReturn([$poll]);
        $this->happening->hasSpeaker($this->pollAuthor)->willReturn(true);
        $this->canUserVoteOnPoll->isSatisfiedBy($poll, $this->pollAuthor)->willReturn(false);
        $this->getHappeningPollHandler->handle(new GetHappeningPoll($poll, true))->willReturn($pollView);

        $expected = [$pollView];

        $result = $this->getHappeningPollsHandler->handle(
            new GetHappeningPolls($this->happening->reveal(),
                                  $this->pollAuthor,
                                  'fr',
                                  null
            )
        );

        self::assertEquals($expected, $result);
    }

    private function createPoll(): Poll
    {
        $poll = new Poll(
            $this->happening->reveal(),
            $this->pollAuthor,
            $this->dateTime,
            'How do you feel today?',
            [
                ['id' => 101, 'content' => 'Good'],
                ['id' => 102, 'content' => 'Bad'],
            ],
            false
        );

        $reflection = new \ReflectionClass(Poll::class);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($poll, 1);
        $property->setAccessible(false);

        $property = $reflection->getProperty('pollChoices');
        $property->setAccessible(true);
        $property->setValue($poll, new ArrayCollection([
            $this->createPollChoice($poll, 'Option #1', 11),
            $this->createPollChoice($poll, 'Option #2', 12),
        ]));
        $property->setAccessible(false);

        return $poll;
    }

    private function createPollChoice(Poll $poll, $content, $id): PollChoice
    {
        $pollChoice = new PollChoice(
            $poll,
            $content
        );

        $reflection = new \ReflectionClass(PollChoice::class);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($pollChoice, $id);
        $property->setAccessible(false);

        return $pollChoice;
    }
}
