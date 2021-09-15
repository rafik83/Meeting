<?php

namespace Proximum\Vimeet\Application\Query\Happening;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Components\Sheet\SheetGuesser;
use Proximum\Vimeet\Application\Exception\Happening\EmptyHappeningParticipationException;
use Proximum\Vimeet\Application\Query\Happening\Admin\HappeningParticipantExportViewQuery;
use Proximum\Vimeet\Application\Query\Happening\Admin\HappeningParticipantExportViewQueryHandler;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\HappeningParticipation;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Happening\QuestionRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\HappeningParticipationRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\HappeningRepositoryInterface;
use Proximum\Vimeet\Domain\Service\SheetsGroup\GroupNameResolver;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;

class HappeningParticipantExportViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event     = EventFactory::createEvent();
        $locale    = 'fr';

        $user      = $this->prophesize(User::class);
        $user->getId()->willReturn(1);
        $user->getFirstName()->willReturn('john');
        $user->getLastName()->willReturn('doh');
        $user->getEmail()->willReturn('johndoh@gmail.com');
        $user->getPosition()->willReturn('ceo');
        $user->getPhone()->willReturn('0134345656');

        $sheet     = SheetFactory::create($event, $user->reveal());
        $happening = $this->prophesize(Happening::class);
        $happening->getId()->willReturn(512);
        $happening->getTitle($locale)->willReturn('My happening');
        $happening->getBegin()->willReturn(\DateTime::createFromFormat('!Y-m-d H:i', '2020-11-09 02:00'));
        $happening->getEnd()->willReturn(\DateTime::createFromFormat('!Y-m-d H:i', '2020-11-09 03:00'));

        $participation = new HappeningParticipation($happening->reveal(), $user->reveal());
        $participation->setEvaluation(4);
        $happening->getParticipations()->shouldBeCalled()->willReturn([$participation]);

        // Mock
        $happeningRepository = $this->prophesize(HappeningRepositoryInterface::class);
        $questionRepository  = $this->prophesize(QuestionRepositoryInterface::class);
        $groupNameResolver   = $this->prophesize(GroupNameResolver::class);
        $sheetGuesser        = $this->prophesize(SheetGuesser::class);
        $happeningParticipationRepository = $this->prophesize(HappeningParticipationRepositoryInterface::class);

        $happeningRepository->findHappeningParticipant($event)->shouldBeCalled()->willReturn([$happening->reveal()]);

        $question1 = $this->prophesize(Happening\Question::class);
        $question1->getContent()->shouldBeCalled()->willReturn('Question 1 ?');
        $question1->getAskedDuringWEbinar()->shouldBeCalled()->willReturn(false);
        $question2 = $this->prophesize(Happening\Question::class);
        $question2->getContent()->shouldBeCalled()->willReturn('Question 2 ?');
        $question2->getAskedDuringWebinar()->shouldBeCalled()->willReturn(true);
        $question2->getReplyContent()->shouldBeCalled()->willReturn('Réponse question 2');
        $question2->getCreatedAt()->shouldBeCalled()->willReturn(\DateTime::createFromFormat('!Y-m-d H:i', '2020-11-09 02:00'));

        $questionRepository
            ->findByHappeningAndUser($happening, $user->reveal())
            ->shouldBeCalled()
            ->willReturn([[$question1->reveal(), 0], [$question2->reveal(), 2]])
        ;

        $groupNameResolver->resolve($event, $user)->shouldBeCalled()->willReturn('');
        $sheetGuesser->getUserSheet($user, $event, $locale)->shouldBeCalled()->willReturn($sheet);

        $handler = new HappeningParticipantExportViewQueryHandler(
            $happeningRepository->reveal(),
            $questionRepository->reveal(),
            $groupNameResolver->reveal(),
            $sheetGuesser->reveal(),
            $happeningParticipationRepository->reveal()
        );

        $happeningParticipantListView = $handler->handle(
            new HappeningParticipantExportViewQuery($event, $locale)
        );

        $this->assertCount(1, $happeningParticipantListView->getHappeningParticipantListView());
        $happeningParticipantView = $happeningParticipantListView->getHappeningParticipantListView()[0];

        $this->assertEquals('john', $happeningParticipantView->getFirstname());
        $this->assertEquals('doh', $happeningParticipantView->getLastname());
        $this->assertEquals('ceo', $happeningParticipantView->getPosition());
        $this->assertEquals('johndoh@gmail.com', $happeningParticipantView->getEmail());
        $this->assertEquals("Question 1 ?", $happeningParticipantView->getQuestionRegister());
        $this->assertEquals("Question 2 ?", $happeningParticipantView->getQuestionsWebinar());
        $this->assertEquals("Réponse question 2", $happeningParticipantView->getReplies());
        $this->assertEquals("2", $happeningParticipantView->getVotes());
        $this->assertEquals(4, $happeningParticipantView->getEvaluation());
    }

    public function testHandleEmptyParticipation()
    {
        $this->expectException(EmptyHappeningParticipationException::class);

        $event  = EventFactory::createEvent();
        $locale = 'fr';

        // Mock
        $happeningRepository = $this->prophesize(HappeningRepositoryInterface::class);
        $questionRepository  = $this->prophesize(QuestionRepositoryInterface::class);
        $groupNameResolver   = $this->prophesize(GroupNameResolver::class);
        $sheetGuesser        = $this->prophesize(SheetGuesser::class);
        $happeningParticipation = $this->prophesize(HappeningParticipationRepositoryInterface::class);

        $happeningRepository->findHappeningParticipant($event)->shouldBeCalled()->willReturn([]);

        $handler = new HappeningParticipantExportViewQueryHandler(
            $happeningRepository->reveal(),
            $questionRepository->reveal(),
            $groupNameResolver->reveal(),
            $sheetGuesser->reveal(),
            $happeningParticipation->reveal()
        );

        $this->expectException(EmptyHappeningParticipationException::class);

        $handler->handle(new HappeningParticipantExportViewQuery($event, $locale));
    }
}
