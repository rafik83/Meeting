<?php

namespace Proximum\Vimeet\Tests\Application\Query\Happening\Webinar\Question;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Query\Happening\Webinar\Question\GetHappeningQuestions;
use Proximum\Vimeet\Application\Query\Happening\Webinar\Question\GetHappeningQuestionsHandler;
use Proximum\Vimeet\Application\Query\Happening\Webinar\Question\QuestionView;
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

    /** @var ObjectProphecy|QuestionRepositoryInterface */
    private $questionRepository;

    protected function setUp()
    {
        $this->questionRepository = $this->prophesize(QuestionRepositoryInterface::class);
        $this->getHappeningQuestionsHandler = new GetHappeningQuestionsHandler($this->questionRepository->reveal());
    }

    public function test_get_questions_list()
    {
        $happening = $this->prophesize(Happening::class);

        $event = EventFactory::createEvent();

        $user1 = $this->prophesize(User::class);
        $user1->getFirstName()
            ->shouldBeCalled()
            ->willReturn('Jean');
        $user1->getLastName()
            ->shouldBeCalled()
            ->willReturn('Dupond');
        $user1->getAvatar()
            ->shouldBeCalled()
            ->willReturn(null);

        $user2 = $this->prophesize(User::class);
        $user2->getFirstName()
            ->shouldBeCalled()
            ->willReturn('George');
        $user2->getLastName()
            ->shouldBeCalled()
            ->willReturn('DOE');
        $user2->getAvatar()
            ->shouldBeCalled()
            ->willReturn(null);

        $sheet1 = SheetFactory::create($event, $user1->reveal());
        $sheet1->setTitle('World Company');

        $sheet2 = SheetFactory::create($event, $user2->reveal());
        $sheet2->setTitle('Cola inc.');

        $question1 = new Question($happening->reveal(), $sheet1, $user1->reveal(), new \DateTime('2020-06-01 17:00:00'), 'The solution is already deployed?');
        $question2 = new Question($happening->reveal(), $sheet2, $user2->reveal(), new \DateTime('2020-05-29 15:00:00'), 'What is the environmental impact of the AI?');

        $this->questionRepository
            ->getByHappeningDuringWebinar($happening->reveal())
            ->shouldBeCalled()
            ->willReturn([$question1, $question2]);

        $result = $this->getHappeningQuestionsHandler->handle(new GetHappeningQuestions($happening->reveal()));

        $this->assertEquals(
            [
                new QuestionView(
                    'The solution is already deployed?',
                    'Jean',
                    'Dupond',
                    null,
                    'World Company',
                    new \DateTime('2020-06-01 17:00:00')
                ),
                new QuestionView(
                    'What is the environmental impact of the AI?',
                    'George',
                    'DOE',
                    null,
                    'Cola inc.',
                    new \DateTime('2020-05-29 15:00:00')
                )
            ],
            $result
        );
    }
}
