<?php

namespace Proximum\Vimeet\Tests\Application\Query\Happening\Webinar\Question;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Happening\Webinar\Question\GetHappeningQuestions;
use Proximum\Vimeet\Application\Query\Happening\Webinar\Question\GetHappeningQuestionsHandler;
use Proximum\Vimeet\Application\Query\Happening\Webinar\Question\QuestionView;
use Proximum\Vimeet\Domain\Model\Happening;

class GetHappeningQuestionsHandlerTest extends TestCase
{
    /** @var GetHappeningQuestionsHandler */
    private $getHappeningQuestionsHandler;

    protected function setUp()
    {
        $this->getHappeningQuestionsHandler = new GetHappeningQuestionsHandler();
    }

    public function test_get_questions_list()
    {
        $happening = $this->prophesize(Happening::class);

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
                    'What is the environmental impact of the IA?',
                    'George',
                    'Doe',
                    null,
                    'Cola inc.',
                    new \DateTime('2020-05-29 15:00:00')
                )
            ],
            $result
        );
    }
}
