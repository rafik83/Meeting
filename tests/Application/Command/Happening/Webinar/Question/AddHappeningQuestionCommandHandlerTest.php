<?php

namespace Application\Command\Happening\Webinar\Question;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Happening\Webinar\Question\AddHappeningQuestionCommand;
use Proximum\Vimeet\Application\Command\Happening\Webinar\Question\AddHappeningQuestionCommandHandler;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Happening\Question;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Happening\QuestionRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;

class AddHappeningQuestionCommandHandlerTest extends TestCase
{
    /** @var ObjectProphecy|QuestionRepositoryInterface */
    private $questionRepository;

    /** @var AddHappeningQuestionCommandHandler */
    private $addQuestionHandler;

    public function setUp()
    {
        $this->questionRepository = $this->prophesize(QuestionRepositoryInterface::class);
        $this->addHappeningQuestionHandler = new AddHappeningQuestionCommandHandler($this->questionRepository->reveal());
    }

    public function test_add_happening_question()
    {
        $happening = $this->prophesize(Happening::class);

        $createdBy = $this->prophesize(User::class);

        $sheet = SheetFactory::create(EventFactory::createEvent(), $createdBy->reveal());

        $t = new \DateTime('2020-06-02 12:00:00');

        $addHappeningQuestionCommand = $this->prophesize(AddHappeningQuestionCommand::class);
        $addHappeningQuestionCommand->getHappening()
            ->shouldBeCalled()
            ->willReturn($happening->reveal());
        $addHappeningQuestionCommand->getSheet()
            ->shouldBeCalled()
            ->willReturn($sheet);
        $addHappeningQuestionCommand->getCreatedBy()
            ->shouldBeCalled()
            ->willReturn($createdBy->reveal());
        $addHappeningQuestionCommand->getContent()
            ->shouldBeCalled()
            ->willReturn('Can you develop your point about green IT?');
        $addHappeningQuestionCommand->getCreatedAt()
            ->shouldBeCalled()
            ->willReturn($t);

        $expectedQuestion = new Question(
            $happening->reveal(),
            $sheet,
            $createdBy->reveal(),
            $t,
            'Can you develop your point about green IT?'
        );

        $this->questionRepository->add($expectedQuestion)
            ->shouldBeCalled();

        $this->addHappeningQuestionHandler->handle($addHappeningQuestionCommand->reveal());
    }
}
