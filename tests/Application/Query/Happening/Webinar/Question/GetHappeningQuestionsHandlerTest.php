<?php

namespace Proximum\Vimeet\Tests\Application\Query\Happening\Webinar\Question;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Query\Happening\Webinar\Question\GetHappeningQuestions;
use Proximum\Vimeet\Application\Query\Happening\Webinar\Question\GetHappeningQuestionsHandler;
use Proximum\Vimeet\Application\Query\Happening\Webinar\Question\QuestionReplyView;
use Proximum\Vimeet\Application\Query\Happening\Webinar\Question\QuestionView;
use Proximum\Vimeet\Domain\Event\GetTimezoneHelper;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Happening\Question;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Happening\QuestionRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;

class GetHappeningQuestionsHandlerTest extends TestCase
{
    /** @var GetHappeningQuestionsHandler */
    private $getHappeningQuestionsHandler;

    /** @var ObjectProphecy */
    private $questionRepository;

    /** @var ObjectProphecy */
    private $getTimezoneHelper;

    protected function setUp()
    {
        $this->questionRepository = $this->prophesize(QuestionRepositoryInterface::class);
        $this->getTimezoneHelper = $this->prophesize(GetTimezoneHelper::class);

        $this->getHappeningQuestionsHandler = new GetHappeningQuestionsHandler(
            $this->questionRepository->reveal(),
            $this->getTimezoneHelper->reveal()
        );
    }

    public function test_get_questions_list()
    {
        $event = EventFactory::createEvent();

        $happening = $this->prophesize(Happening::class);
        $happening->getEvent()->shouldBeCalled()->willReturn($event);

        $user1 = $this->prophesize(User::class);
        $user1->getId()->shouldBeCalled()->willReturn(1);
        $user1->getFirstName()
            ->shouldBeCalled()
            ->willReturn('Jean');
        $user1->getLastName()
            ->shouldBeCalled()
            ->willReturn('Dupond');
        $user1->getAvatar()
            ->shouldBeCalled()
            ->willReturn(null);
        $user1->getPosition()
            ->shouldBeCalled()
            ->willReturn(null);

        $user2 = $this->prophesize(User::class);
        $user2->getId()->shouldBeCalled()->willReturn(2);
        $user2->getFirstName()
            ->shouldBeCalled()
            ->willReturn('George');
        $user2->getLastName()
            ->shouldBeCalled()
            ->willReturn('DOE');
        $user2->getAvatar()
            ->shouldBeCalled()
            ->willReturn(null);
        $user2->getPosition()
            ->shouldBeCalled()
            ->willReturn('Employee');

        $sheet1 = SheetFactory::create($event, $user1->reveal());
        $sheet1->setTitle('World Company');

        $sheet2 = SheetFactory::create($event, $user2->reveal());
        $sheet2->setTitle('Cola inc.');

        $question1 = $this->prophesize(Question::class);
        $question1->getId()->shouldBeCalled()->willReturn(1);
        $question1->getSheet()->shouldBeCalled()->willReturn($sheet1);
        $question1->getCreatedBy()->shouldBeCalled()->willReturn($user1->reveal());
        $question1->getCreatedAt()->shouldBeCalled()->willReturn(new \DateTime('2020-06-01 17:23:42'));
        $question1->getContent()->shouldBeCalled()->willReturn('The solution is already deployed?');
        $question1->getReplyContent()->shouldBeCalled()->willReturn(null);
        $question1->getRepliedBy()->shouldNotBeCalled();
        $question1->getRepliedAt()->shouldNotBeCalled();

        $question2 = $this->prophesize(Question::class);
        $question2->getId()->shouldBeCalled()->willReturn(42);
        $question2->getSheet()->shouldBeCalled()->willReturn($sheet2);
        $question2->getCreatedBy()->shouldBeCalled()->willReturn($user2->reveal());
        $question2->getCreatedAt()->shouldBeCalled()->willReturn(new \DateTime('2020-05-29 08:00:00'));
        $question2->getContent()->shouldBeCalled()->willReturn('What is the environmental impact of the AI?');
        $question2->getReplyContent()->shouldBeCalled()->willReturn('This is a good question, not easy to answer');
        $question2ReplyAuthor = $this->prophesize(User::class);
        $question2ReplyAuthor->getFullName()->willReturn('Greta Thunberg');
        $question2ReplyAuthor->getId()->willReturn(314);
        $question2->getRepliedBy()->shouldBeCalled()->willReturn($question2ReplyAuthor->reveal());
        $question2->getRepliedAt()->willReturn(new \DateTime('2020-01-01 03:14:15'));

        $this->getTimezoneHelper
            ->getTimezoneByEventAndUser($event, $user1->reveal())
            ->shouldBeCalled()
            ->willReturn('Europe/Paris')
        ;
        $this->getTimezoneHelper
            ->getTimezoneByEventAndUser($event, $question2ReplyAuthor->reveal())
            ->willReturn('Europe/Paris')
        ;

        $this->questionRepository
            ->getByHappeningDuringWebinar($happening->reveal(), $user1->reveal())
            ->shouldBeCalled()
            ->willReturn([
                [$question1, 0, 0],
                [$question2, 2, 1],
            ]);

        $result = $this->getHappeningQuestionsHandler->handle(
            new GetHappeningQuestions($happening->reveal(), $user1->reveal(), 'en')
        );

        $this->assertEquals(
            [
                new QuestionView(
                    1,
                    'The solution is already deployed?',
                    'Jean',
                    'Dupond',
                    null,
                    null,
                    'World Company',
                    '7:23:42 PM',
                    0,
                    false,
                    false,
                    null
                ),
                new QuestionView(
                    42,
                    'What is the environmental impact of the AI?',
                    'George',
                    'DOE',
                    'Employee',
                    null,
                    'Cola inc.',
                    '10:00:00 AM',
                    2,
                    true,
                    true,
                    new QuestionReplyView('This is a good question, not easy to answer', 'Greta Thunberg', '4:14:15 AM', false)
                )
            ],
            $result
        );
    }

    public function test_get_question_updatable()
    {
        $event = EventFactory::createEvent();

        $happening = $this->prophesize(Happening::class);
        $happening->getEvent()->shouldBeCalled()->willReturn($event);

        $user = $this->prophesize(User::class);
        $user->getId()->shouldBeCalled()->willReturn(1);
        $user->getFullName()->willReturn('Jean Dupond');

        $sheet = SheetFactory::create($event, $user->reveal());
        $sheet->setTitle('World Company');

        $question = $this->prophesize(Question::class);
        $question->getId()->shouldBeCalled()->willReturn(1);
        $question->getSheet()->shouldBeCalled()->willReturn($sheet);
        $questionAuthor = $this->prophesize(User::class);
        $questionAuthor->getId()->willReturn(1234);
        $questionAuthor->getFirstName()->willReturn('Donald');
        $questionAuthor->getLastName()->willReturn('Duck');
        $questionAuthor->getAvatar()->willReturn(null);
        $questionAuthor->getPosition()->willReturn(null);
        $question->getCreatedBy()->shouldBeCalled()->willReturn($questionAuthor->reveal());
        $question->getCreatedAt()->shouldBeCalled()->willReturn(new \DateTime('2020-06-01 17:23:42'));
        $question->getContent()->shouldBeCalled()->willReturn('The solution is already deployed?');
        $question->getReplyContent()->shouldBeCalled()->willReturn('Yes');
        $question->getRepliedBy()->shouldBeCalled()->willReturn($user->reveal());
        $question->getRepliedAt()->willReturn(new \DateTime('2020-01-01 03:14:15'));

        $this->getTimezoneHelper
            ->getTimezoneByEventAndUser($event, $user->reveal())
            ->shouldBeCalled()
            ->willReturn('Europe/Paris')
        ;

        $this->questionRepository
            ->getByHappeningDuringWebinar($happening->reveal(), $user->reveal())
            ->shouldBeCalled()
            ->willReturn([
                [$question, 0, 0],
            ]);

        $result = $this->getHappeningQuestionsHandler->handle(
            new GetHappeningQuestions($happening->reveal(), $user->reveal(), 'en')
        );

        $this->assertEquals(
            [
                new QuestionView(
                    1,
                    'The solution is already deployed?',
                    'Donald',
                    'Duck',
                    null,
                    null,
                    'World Company',
                    '7:23:42 PM',
                    0,
                    false,
                    true,
                    new QuestionReplyView('Yes', 'Jean Dupond', '4:14:15 AM', true)
                ),
            ],
            $result
        );
    }
}
