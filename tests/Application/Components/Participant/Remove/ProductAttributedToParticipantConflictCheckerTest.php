<?php

namespace Proximum\Vimeet\Tests\Application\Components\Participant\Remove;

use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Components\Participant\Remove\ConflictsView;
use Proximum\Vimeet\Application\Components\Participant\Remove\ParticipantConflictView;
use Proximum\Vimeet\Application\Components\Participant\Remove\ProductAttributedToParticipantConflictChecker;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Model\CartRowParticipant;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\ProductAttributedToParticipant;
use Proximum\Vimeet\Domain\Repository\CartRowParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\ProductAttributedToParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;

class ProductAttributedToParticipantConflictCheckerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $cartRowParticipantRepository;

    /** @var ObjectProphecy */
    private $participantInfoGuesser;

    /** @var ObjectProphecy */
    private $productAttributedToParticipantRepository;

    /** @var string */
    private $locale;

    /** @var ObjectProphecy */
    private $participant1;

    /** @var ObjectProphecy */
    private $participant2;

    /** @var ObjectProphecy */
    private $participant3;

    /** @var ObjectProphecy[] */
    private $participants;

    public function setUp()
    {
        $this->cartRowParticipantRepository = $this->prophesize(CartRowParticipantRepositoryInterface::class);
        $this->participantInfoGuesser = $this->prophesize(ParticipantInfoGuesser::class);
        $this->productAttributedToParticipantRepository = $this->prophesize(ProductAttributedToParticipantRepositoryInterface::class);

        $this->participant1 = $this->prophesize(Participant::class);
        $this->participant2 = $this->prophesize(Participant::class);
        $this->participant3 = $this->prophesize(Participant::class);


        $this->participant1->getId()->willReturn(11);
        $this->participant2->getId()->willReturn(12);
        $this->participant3->getId()->willReturn(13);

        $this->locale = 'fr';
        $this->participants = [
            $this->participant1->reveal(),
            $this->participant2->reveal(),
            $this->participant3->reveal(),
        ];
    }
    public function testWithConflictOnAttributedProduct(): void
    {
        $productAttributed1 = $this->prophesize(ProductAttributedToParticipant::class);
        $productAttributed2 = $this->prophesize(ProductAttributedToParticipant::class);
        $productAttributed3 = $this->prophesize(ProductAttributedToParticipant::class);

        $productAttributed1->getParticipant()->willReturn($this->participant1->reveal());
        $productAttributed2->getParticipant()->willReturn($this->participant3->reveal());
        $productAttributed3->getParticipant()->willReturn($this->participant1->reveal());

        $this->productAttributedToParticipantRepository
            ->findByParticipants($this->participants)
            ->shouldBeCalled()
            ->willReturn([
                $productAttributed1->reveal(),
                $productAttributed2->reveal(),
                $productAttributed3->reveal(),
            ])
        ;

        $this->participantInfoGuesser
            ->guessParticipantCompleteName($this->participant1->reveal(), $this->locale)
            ->shouldBeCalled()
            ->willReturn('Jean Michel')
        ;

        $this->participantInfoGuesser
            ->guessParticipantCompleteName($this->participant3->reveal(), $this->locale)
            ->shouldBeCalled()
            ->willReturn('Paul Michel')
        ;

        $productAttributedToParticipantConflictChecker = new ProductAttributedToParticipantConflictChecker(
            $this->productAttributedToParticipantRepository->reveal(),
            $this->cartRowParticipantRepository->reveal(),
            $this->participantInfoGuesser->reveal()
        );

        $result = $productAttributedToParticipantConflictChecker
            ->getParticipantsWithConflictOnProductAttributed(
                $this->participants,
                $this->locale
            )
        ;

        $expected = new ConflictsView();
        $expected->addConflict(new ParticipantConflictView(11, 'Jean Michel'));
        $expected->addConflict(new ParticipantConflictView(13, 'Paul Michel'));

        $this->assertInstanceOf(ConflictsView::class, $result);
        $this->assertEquals($expected, $result);
    }

    public function testWithConflictOnCartRowParticipant(): void
    {
        $this->productAttributedToParticipantRepository
            ->findByParticipants($this->participants)
            ->shouldBeCalled()
            ->willReturn([])
        ;

        $cartRowParticipant = $this->prophesize(CartRowParticipant::class);
        $cartRowParticipant->getParticipant()->willReturn($this->participant2->reveal());

        $this->cartRowParticipantRepository
            ->findCartRowOnAttributableProductForParticipants($this->participants)
            ->shouldBeCalled()
            ->willReturn([$cartRowParticipant])
        ;

        $this->participantInfoGuesser
            ->guessParticipantCompleteName($this->participant2->reveal(), $this->locale)
            ->shouldBeCalled()
            ->willReturn('Thierry Michel')
        ;

        $productAttributedToParticipantConflictChecker = new ProductAttributedToParticipantConflictChecker(
            $this->productAttributedToParticipantRepository->reveal(),
            $this->cartRowParticipantRepository->reveal(),
            $this->participantInfoGuesser->reveal()
        );

        $result = $productAttributedToParticipantConflictChecker
            ->getParticipantsWithConflictOnProductAttributed(
                $this->participants,
                $this->locale
            )
        ;

        $expected = new ConflictsView();
        $expected->addConflict(new ParticipantConflictView(12, 'Thierry Michel'));

        $this->assertInstanceOf(ConflictsView::class, $result);
        $this->assertEquals($expected, $result);
    }

    public function testWithoutConflict(): void
    {
        $this->productAttributedToParticipantRepository
            ->findByParticipants($this->participants)
            ->shouldBeCalled()
            ->willReturn([])
        ;

        $this->cartRowParticipantRepository
            ->findCartRowOnAttributableProductForParticipants($this->participants)
            ->shouldBeCalled()
            ->willReturn([])
        ;

        $productAttributedToParticipantConflictChecker = new ProductAttributedToParticipantConflictChecker(
            $this->productAttributedToParticipantRepository->reveal(),
            $this->cartRowParticipantRepository->reveal(),
            $this->participantInfoGuesser->reveal()
        );

        $result = $productAttributedToParticipantConflictChecker
            ->getParticipantsWithConflictOnProductAttributed(
                $this->participants,
                $this->locale
            )
        ;

        $expected = new ConflictsView();
        $this->assertInstanceOf(ConflictsView::class, $result);
        $this->assertEquals($expected, $result);
    }
}
