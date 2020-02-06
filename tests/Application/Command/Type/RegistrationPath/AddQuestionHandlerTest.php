<?php

namespace Proximum\Vimeet\Tests\Application\Command\Type\RegistrationPath;

use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Command\Type\RegistrationPath\AddQuestion;
use Proximum\Vimeet\Application\Command\Type\RegistrationPath\AddQuestionHandler;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\RegistrationPath\Question;
use Proximum\Vimeet\Domain\Repository\RegistrationPath\QuestionRepositoryInterface;
use Proximum\Vimeet\Domain\Type\RegistrationPath\View\AnswerView;

class AddQuestionHandlerTest extends TestCase
{
    /** @var ObjectProphecy|QuestionRepositoryInterface */
    private $questionRepository;

    /** @var AddQuestionHandler */
    private $addQuestionHandler;

    public function setUp()
    {
        $this->questionRepository = $this->prophesize(QuestionRepositoryInterface::class);
        $this->addQuestionHandler = new AddQuestionHandler($this->questionRepository->reveal());
    }

    public function test_adding_question()
    {
        $event = $this->prophesize(Event::class);
        $event->getLocales()->shouldBeCalled()->willReturn(['fr', 'en']);

        $addQuestion = new AddQuestion($event->reveal(), null);
        $addQuestion->translatedTitle = [
            'fr' => 'Souhaitez-vous faire des RDV ?',
            'en' => 'Would you like to make meetings?'
        ];

        $answerView1 = new AnswerView();
        $answerView1->translatedTitle = [
            'fr' => 'Oui',
            'en' => 'Yes'
        ];

        $answerView2 = new AnswerView();
        $answerView2->translatedTitle = [
            'fr' => 'Non',
            'en' => 'No'
        ];

        $addQuestion->answers = [$answerView1, $answerView2];

        $expectedQuestion = new Question($event->reveal(), null);
        $expectedQuestion->translate('fr', 'Souhaitez-vous faire des RDV ?');
        $expectedQuestion->translate('en', 'Would you like to make meetings?');
        $expectedQuestion->setAnswers([$answerView1, $answerView2]);
        $this->questionRepository->add($expectedQuestion)->shouldBeCalled();

        $this->addQuestionHandler->handle($addQuestion);
    }
}
