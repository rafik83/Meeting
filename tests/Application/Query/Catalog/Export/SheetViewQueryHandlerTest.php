<?php

namespace Proximum\Vimeet\Tests\Application\Query\Catalog\Export;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Application\Query\Catalog\Export\SheetInfoQuery;
use Proximum\Vimeet\Application\Query\Catalog\Export\SheetInfoQueryHandler;
use Proximum\Vimeet\Application\Query\Catalog\Export\SheetRegistrationInfoQuery;
use Proximum\Vimeet\Application\Query\Catalog\Export\SheetRegistrationInfoQueryHandler;
use Proximum\Vimeet\Application\Query\Catalog\Export\SheetViewQuery;
use Proximum\Vimeet\Application\Query\Catalog\Export\SheetViewQueryHandler;
use Proximum\Vimeet\Application\View\Catalog\Export\SheetView;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Rule;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\RuleRepositoryInterface;
use Proximum\Vimeet\Domain\Rule\Applyer;
use Proximum\Vimeet\Domain\Rule\ComposedRule;
use Proximum\Vimeet\Domain\Rule\Composer;
use Proximum\Vimeet\Domain\Service\Category\CategoryNameResolver;
use Proximum\Vimeet\Domain\Sheet\CanSeeSheet;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Domain\Template\TemplateObject\EditableText;
use Proximum\Vimeet\Domain\Template\TemplateObject\Nomenclature;

class SheetViewQueryHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $templateDataFactory;

    /** @var ObjectProphecy */
    private $participantInfoGuesser;

    /** @var ObjectProphecy */
    private $categoryNameResolver;

    /** @var ObjectProphecy */
    private $ruleRepository;

    /** @var ObjectProphecy */
    private $composer;

    /** @var ObjectProphecy */
    private $applyer;

    /** @var ObjectProphecy */
    private $sheetInfoQueryHandler;

    /** @var ObjectProphecy */
    private $sheetRegistrationInfoQueryHandler;

    /** @var ObjectProphecy */
    private $sheet;

    /** @var ObjectProphecy */
    private $viewer;

    /** @var ObjectProphecy */
    private $type1;

    /** @var ObjectProphecy */
    private $type2;

    /** @var ObjectProphecy */
    private $registrationTemplate;

    /** @var ObjectProphecy */
    private $template;

    public function setUp()
    {
        $this->templateDataFactory = $this->prophesize(TemplateDataFactory::class);
        $this->participantInfoGuesser = $this->prophesize(ParticipantInfoGuesser::class);
        $this->categoryNameResolver = $this->prophesize(CategoryNameResolver::class);
        $this->ruleRepository = $this->prophesize(RuleRepositoryInterface::class);
        $this->composer = $this->prophesize(Composer::class);
        $this->applyer = $this->prophesize(Applyer::class);
        $this->sheetInfoQueryHandler = $this->prophesize(SheetInfoQueryHandler::class);
        $this->sheetRegistrationInfoQueryHandler = $this->prophesize(SheetRegistrationInfoQueryHandler::class);
        $this->sheet = $this->prophesize(Sheet::class);
        $this->viewer = $this->prophesize(Sheet::class);
        $this->type1 = $this->prophesize(Type::class);
        $this->type2 = $this->prophesize(Type::class);
        $this->sheet->getType()->willReturn($this->type1->reveal());
        $this->viewer->getType()->willReturn($this->type2->reveal());
        $this->registrationTemplate = $this->prophesize(TemplateData::class);
        $this->template = $this->prophesize(TemplateData::class);
    }

    public function testHandle()
    {
        $rule1 = $this->prophesize(Rule::class);

        $this->ruleRepository
            ->getBySeerSheetAndSeeableSheet($this->viewer->reveal(), $this->sheet->reveal())
            ->shouldBeCalled()
            ->willReturn([$rule1->reveal()])
        ;

        $requestRepository = $this->prophesize(RequestRepositoryInterface::class);

        $this->templateDataFactory
            ->createRegistrationFromSheet($this->sheet->reveal(), 'fr')
            ->shouldBeCalled()
            ->willReturn($this->registrationTemplate->reveal())
        ;
        $this->templateDataFactory
            ->createFromSheet($this->sheet->reveal(), 'fr')
            ->shouldBeCalled()
            ->willReturn($this->template->reveal())
        ;
        $this->template->getTags()->shouldBeCalled()->willReturn([
            Tag::SHEET_TITLE => Tag::SHEET_TITLE,
            Tag::SHEET_CITY => Tag::SHEET_CITY,
            Tag::SHEET_WEBSITE => Tag::SHEET_WEBSITE,
        ]);

        $object1 = $this->prophesize(Nomenclature::class);
        $object2 = $this->prophesize(EditableText::class);
        $this->registrationTemplate->getExportableObjects()->willReturn([$object1->reveal(), $object2->reveal()]);
        $object1->getTags()->willReturn([]);
        $object2->getTags()->willReturn([
            ['tag' => Tag::SHEET_TITLE],
            ['tag' => Tag::SHEET_ORGANIZATION],
        ]);
        $object1->getKey()->willReturn('azerty123');
        $this->registrationTemplate->removeObject('azerty123')->shouldBeCalled();

        $this->applyer
            ->applyRuleForRegistrationTemplate($this->registrationTemplate->reveal(), [$rule1->reveal()])
            ->shouldBeCalled()
        ;
        $this->applyer->applyRuleForTemplate($this->template->reveal(), [$rule1->reveal()])->shouldBeCalled();

        $composedRule = $this->prophesize(ComposedRule::class);
        $composedRule->isPresent(Tag::PARTICIPANT_POSITION)->shouldBeCalled()->willReturn(true);
        $this->composer->compose([$rule1->reveal()])->shouldBeCalled()->willReturn($composedRule->reveal());

        $participant1 = $this->prophesize(Participant::class);
        $participant2 = $this->prophesize(Participant::class);

        $this->sheet->getParticipantsArray()->willReturn([$participant1->reveal(), $participant2->reveal()]);

        $this->participantInfoGuesser->guessParticipantPositionLabel($participant1->reveal(), 'fr')
            ->shouldBeCalled()
            ->willReturn('President')
        ;

        $this->participantInfoGuesser->guessParticipantPositionLabel($participant2->reveal(), 'fr')
            ->shouldBeCalled()
            ->willReturn('Directeur Technique')
        ;

        $this->type1->getTitle('fr')->willReturn('Exposant');

        $taggedData = [Tag::SHEET_TITLE => 'toto'];
        $this->registrationTemplate->getAllTaggedDatas()->shouldBeCalled()->willReturn($taggedData);

        $this->sheetRegistrationInfoQueryHandler
            ->handle(new SheetRegistrationInfoQuery($this->registrationTemplate->reveal(), 'fr', 'de'))
            ->shouldBeCalled()
            ->willReturn(['azerty123' => 'Aanera'])
        ;
        $this->sheetInfoQueryHandler
            ->handle(new SheetInfoQuery($this->template->reveal(), $taggedData, 'fr', 'de'))
            ->shouldBeCalled()
            ->willReturn(['ytreza321' => 'Test'])
        ;

        $query = new SheetViewQuery(
            $this->sheet->reveal(),
            $this->viewer->reveal(),
            'fr',
            'de',
            true
        );

        $canSeeSheet = new CanSeeSheet($this->ruleRepository->reveal(), $requestRepository->reveal());

        $handler = new SheetViewQueryHandler(
            $this->templateDataFactory->reveal(),
            $this->participantInfoGuesser->reveal(),
            $this->categoryNameResolver->reveal(),
            $this->ruleRepository->reveal(),
            $this->composer->reveal(),
            $this->applyer->reveal(),
            $this->sheetInfoQueryHandler->reveal(),
            $this->sheetRegistrationInfoQueryHandler->reveal(),
            $canSeeSheet
        );

        $result = $handler->handle($query);

        $expected = new SheetView(
            'Exposant',
            ['azerty123' => 'Aanera'],
            ['ytreza321' => 'Test'],
            'President, Directeur Technique'
        );

        $this->assertEquals($expected, $result);
    }

    public function testReMapField()
    {
        $sheetFields = [
            'azerty123' => 'azerty123',
            'azerty124' => 'azerty124',
            'azerty125' => 'azerty125',
            'azerty126' => 'azerty126',
        ];

        $registrationFields = [
            'ytreza321' => 'ytreza321',
            'ytreza322' => 'ytreza322',
            'ytreza323' => 'ytreza323',
            'ytreza324' => 'ytreza324',
        ];

        $sheetView = new SheetView(
            'Type title',
            [
                'ytreza321' => 'ytreza321',
                'ytreza324' => 'ytreza324',
            ],
            [
                'azerty123' => 'value1',
                'azerty125' => 'value2',
            ],
            'President'
        );

        $requestRepository  = $this->prophesize(RequestRepositoryInterface::class);

        $this->sheetInfoQueryHandler->getSheetFields()->shouldBeCalled()->willReturn($sheetFields);
        $this->sheetRegistrationInfoQueryHandler
            ->getSheetRegistrationFields()
            ->shouldBeCalled()
            ->willReturn($registrationFields)
        ;

        $canSeeSheet = new CanSeeSheet($this->ruleRepository->reveal(), $requestRepository->reveal());

        $handler = new SheetViewQueryHandler(
            $this->templateDataFactory->reveal(),
            $this->participantInfoGuesser->reveal(),
            $this->categoryNameResolver->reveal(),
            $this->ruleRepository->reveal(),
            $this->composer->reveal(),
            $this->applyer->reveal(),
            $this->sheetInfoQueryHandler->reveal(),
            $this->sheetRegistrationInfoQueryHandler->reveal(),
            $canSeeSheet
        );

        $handler->reMapFields($sheetView);

        $expected = new SheetView(
            'Type title',
            [
                'ytreza321' => 'ytreza321',
                'ytreza322' => '',
                'ytreza323' => '',
                'ytreza324' => 'ytreza324',
            ],
            [
                'azerty123' => 'value1',
                'azerty125' => 'value2',
                'azerty124' => '',
                'azerty126' => '',
            ],
            'President'
        );

        $this->assertEquals($expected, $sheetView);
    }
}
