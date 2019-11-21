<?php

namespace Proximum\Vimeet\Tests\Application\Command\Type\RegistrationPath;

use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Command\Type\RegistrationPath\AddQuestion;
use Proximum\Vimeet\Application\Command\Type\RegistrationPath\AddQuestionHandler;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\RegistrationPath\Question;
use Proximum\Vimeet\Domain\Repository\RegistrationPath\QuestionRepositoryInterface;

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

        $expectedQuestion = new Question($event->reveal());
        $expectedQuestion->translate('fr', 'Souhaitez-vous faire des RDV ?');
        $expectedQuestion->translate('en', 'Would you like to make meetings?');

        $this->questionRepository->add($expectedQuestion)->shouldBeCalled();

        $addQuestion = new AddQuestion($event->reveal());
        $addQuestion->translatedTitle = [
            'fr' => 'Souhaitez-vous faire des RDV ?',
            'en' => 'Would you like to make meetings?'
        ];
        $this->addQuestionHandler->handle($addQuestion);
    }
}
