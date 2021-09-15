<?php

namespace Proximum\Vimeet\Tests\Application\Query\Happening\Webinar\Poll;

use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\NotificationSubscriberInterface;
use Proximum\Vimeet\Application\Query\Happening\Webinar\Poll\GetHappeningPoll;
use Proximum\Vimeet\Application\Query\Happening\Webinar\Poll\GetHappeningPollHandler;
use Proximum\Vimeet\Application\Query\Happening\Webinar\Poll\GetHappeningPollResults;
use Proximum\Vimeet\Application\Query\Happening\Webinar\Poll\GetHappeningPollResultsHandler;
use Proximum\Vimeet\Application\Query\Happening\Webinar\Poll\PollChoiceResultView;
use Proximum\Vimeet\Application\Query\Happening\Webinar\Poll\PollChoiceView;
use Proximum\Vimeet\Application\Query\Happening\Webinar\Poll\PollResultsView;
use Proximum\Vimeet\Application\Query\Happening\Webinar\Poll\PollView;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Happening\Poll;
use Proximum\Vimeet\Domain\Model\Happening\PollChoice;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Poll\CanUserVoteOnPoll;
use Proximum\Vimeet\Domain\Repository\Happening\PollVoteRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\UserFactory;

class GetHappeningPollHandlerTest extends TestCase
{
    private ObjectProphecy $happening;
    private User $pollAuthor;
    private DateTimeInterface $dateTime;
    private ObjectProphecy $canUserVoteOnPoll;
    private ObjectProphecy $getHappeningPollResultsHandler;
    /**
     * @var ObjectProphecy|NotificationSubscriberInterface
     */
    private $notificationSubscriber;
    /**
     * @var ObjectProphecy|PollVoteRepositoryInterface
     */
    private $pollVoteRepository;
    private GetHappeningPollHandler $getHappeningPollHandler;

    protected function setUp(): void
    {
        $this->happening = $this->prophesize(Happening::class);
        $this->pollAuthor = UserFactory::create();
        $this->dateTime = DateTime::createFromFormat('!Y-m-d H:i', '2020-03-14 15:09');

        $this->getHappeningPollResultsHandler = $this->prophesize(GetHappeningPollResultsHandler::class);
        $this->canUserVoteOnPoll = $this->prophesize(CanUserVoteOnPoll::class);
        $this->notificationSubscriber = $this->prophesize(NotificationSubscriberInterface::class);
        $this->pollVoteRepository = $this->prophesize(PollVoteRepositoryInterface::class);

        $this->getHappeningPollHandler = new GetHappeningPollHandler(
            $this->getHappeningPollResultsHandler->reveal(),
            $this->notificationSubscriber->reveal(),
            $this->pollVoteRepository->reveal()
        );
    }

    public function testHandleWithResults(): void
    {
        $poll = $this->createPoll();

        $this->canUserVoteOnPoll->isSatisfiedBy($poll, $this->pollAuthor)->willReturn(false);

        $this->notificationSubscriber->getPollResultsSubscriberKey($poll)->willReturn('foobar');

        $this->pollVoteRepository->hasVotes($poll)->willReturn(true);
        $this->pollVoteRepository->countVotingUsers($poll)->willReturn(5);

        $this->getHappeningPollResultsHandler->handle(new GetHappeningPollResults($poll))
            ->willReturn(
                new PollResultsView(
                    [
                        new PollChoiceResultView(11, 2),
                        new PollChoiceResultView(12, 3),
                    ]
                )
            )
        ;

        $expectedChoices = [
            new PollChoiceView(11, 'Good', 40),
            new PollChoiceView(12, 'Bad', 60),
        ];

        $expected = new PollView(
            1,
            'How do you feel today?',
            $expectedChoices,
            false,
            Poll::STATUS_DRAFT,
            5,
            false,
            'foobar',
            true
        );

        $result = $this->getHappeningPollHandler->handle(new GetHappeningPoll($poll, true));

        self::assertEquals($expected, $result);
    }

    public function testHandleWithoutResults(): void
    {
        $poll = $this->createPoll();

        $this->canUserVoteOnPoll->isSatisfiedBy($poll, $this->pollAuthor)->willReturn(false);

        $this->notificationSubscriber->getPollResultsSubscriberKey($poll)->willReturn('foobar');

        $this->pollVoteRepository->hasVotes($poll)->willReturn(true);

        $this->getHappeningPollResultsHandler->handle(new GetHappeningPollResults($poll))
            ->willReturn(
                new PollResultsView(
                    [
                        new PollChoiceResultView(11, 2),
                        new PollChoiceResultView(12, 3),
                    ]
                )
            )
        ;

        $expectedChoices = [
            new PollChoiceView(11, 'Good', null),
            new PollChoiceView(12, 'Bad', null),
        ];

        $expected = new PollView(
            1,
            'How do you feel today?',
            $expectedChoices,
            false,
            Poll::STATUS_DRAFT,
            0,
            true,
            null,
            true
        );

        $result = $this->getHappeningPollHandler->handle(new GetHappeningPoll($poll, false));

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
                ['id' => 11, 'content' => 'Good'],
                ['id' => 12, 'content' => 'Bad'],
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
        $property->setValue(
            $poll,
            new ArrayCollection(
                [
                    $this->createPollChoice($poll, 'Good', 11),
                    $this->createPollChoice($poll, 'Bad', 12),
                ]
            )
        );
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
