<?php

namespace Proximum\Vimeet\Tests\Application\Query\Happening\Webinar\Question;

use PHPUnit\Framework\TestCase;
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

    /** @var QuestionRepositoryInterface */
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

        $user1 = new User('user1@vimeet.com', '__salt__', '__password__', 'fr');
        $account1 = new User\Account();
        $account1->setFirstName('Jean');
        $account1->setLastName('Dupond');
        $user1->setAccount($account1);

        $user2 = new User('user2@vimeet.com', '__salt__', '__password__', 'fr');
        $account2 = new User\Account();
        $account2->setFirstName('George');
        $account2->setLastName('Doe');
        $user2->setAccount($account2);

        $sheet1 = SheetFactory::create($event, $user1);
        $sheet1->setTitle('World Company');

        $sheet2 = SheetFactory::create($event, $user2);
        $sheet2->setTitle('Cola inc.');

        $question1 = new Question($happening->reveal(), $sheet1, $user1, new \DateTime('2020-06-01 17:00:00'), 'The solution is already deployed?');
        $question2 = new Question($happening->reveal(), $sheet2, $user2, new \DateTime('2020-05-29 15:00:00'), 'What is the environmental impact of the AI?');

        $this->questionRepository
            ->getByHappening($happening->reveal())
            ->shouldBeCalled()
            ->willReturn([$question1, $question2]);

        $result = $this->getHappeningQuestionsHandler->handle(new GetHappeningQuestions($happening->reveal()));

        $this->assertEquals(
            [
                new QuestionView(
                    'The solution is already deployed?',
                    'Jean',
                    'DUPOND',
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
