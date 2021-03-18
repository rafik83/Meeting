<?php

namespace Proximum\Vimeet\Domain\Model\Sheet;

use DateTime;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\SheetCompleteness;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Sheet\SheetCompletenessRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Sheet\CompletenessCalculator;
use Proximum\Vimeet\Domain\Template\Block;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Domain\Template\TemplateObject;
use Proximum\Vimeet\Domain\Template\TemplateObject\EditableText;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;
use Proximum\Vimeet\Tests\Factory\SheetFactory;

class CompletenessCalculatorTest extends TestCase
{
    public function testCalculateFullCompleteness()
    {
        $locales  = ['fr', 'en'];
        $datetime = new DateTime();
        $user     = new User('user@vimeet.com', 'salt', 'password', 'fr');
        $sheet    = SheetFactory::create(null, $user, $datetime);
        $sheet->getEvent()->setLocales($locales);

        $participant = new Participant($sheet, $user, [
            '541f84d4' => [
                'text' => 'text1',
            ],
            '838197c7' => [
                'text' => 'text2',
            ],
            '1efb9cbb' => [
                'telephone' => 'telephone1',
            ],
            '3b759fbb' => [
                'telephone' => 'telephone2',
            ],
        ], true, $datetime);
        $sheet->addParticipant($participant);

        // Sheet Template Data

        $sheetTemplateData  = new TemplateData('root', [], 'fr', 'fr');
        $blockSheetTemplate = new Block('12', [], 'fr', 'fr');
        $title              = new EditableText('69b3cde2', 'editable-text', [
            'required'     => true,
            'translatable' => true,
        ], 'fr', 'fr');
        $title->setData([
            'text' => [
                'fr' => 'titre',
                'en' => 'title',
            ],
        ]);
        $description = new EditableText('69b3cde3', 'editable-text', [
            'required' => true,
        ], 'fr', 'fr');
        $description->setData([
            'text' => 'description',
        ]);

        $blockSheetTemplate->addChild(0, '69b3cde2', $title);
        $blockSheetTemplate->addChild(0, '69b3cde3', $description);
        $sheetTemplateData->addChild(0, '811f6edfa', $blockSheetTemplate);

        // Registration Template Data

        $registrationTemplateData = new TemplateData('root', [], 'fr', 'fr');
        $block                    = new Block('12', [], 'fr', 'fr');
        $text                     = new TemplateObject\Text('dded0597', 'text', [], 'fr', 'fr');
        $editableText1            = new TemplateObject\EditableText('541f84d4', 'editable-text', [
            'tags'     => ['participant_firstname', 'participant_data'],
            'required' => true,
        ], 'fr', 'fr');
        $editableText2            = new TemplateObject\EditableText('838197c7', 'editable-text', [
            'tags'     => ['participant_lastname', 'participant_data'],
            'required' => true,
        ], 'fr', 'fr');
        $telephone1               = new TemplateObject\Telephone('1efb9cbb', 'telephone', [
            'tags'     => ['participant_phone', 'participant_data'],
            'required' => true,
        ], 'fr', 'fr');
        $telephone2               = new TemplateObject\Telephone('3b759fbb', 'telephone', [
            'tags'     => ['participant_mobile', 'participant_data'],
            'required' => true,
        ], 'fr', 'fr');

        $block->addChild(1, 'dded0597', $text);
        $block->addChild(1, '541f84d4', $editableText1);
        $block->addChild(1, '838197c7', $editableText2);
        $block->addChild(1, '1efb9cbb', $telephone1);
        $block->addChild(1, '3b759fbb', $telephone2);
        $registrationTemplateData->addChild(0, '811f6edf', $block);

        // Expected
        $expectedSheet = SheetFactory::create(null, $user, $datetime);
        $expectedSheet->setCompleteness(100);

        // Mock
        $templateDataFactory         = $this->prophesize(TemplateDataFactory::class);
        $sheetRepository             = $this->prophesize(SheetRepositoryInterface::class);
        $sheetCompletenessRepository = $this->prophesize(SheetCompletenessRepositoryInterface::class);
        $eventDispatcher             = $this->prophesize(DelayedEventDispatcher::class);

        $templateDataFactory->createFromSheet($sheet, 'fr')
            ->shouldBeCalled()
            ->willReturn($sheetTemplateData);

        $templateDataFactory->createRegistrationFromSheet($sheet, 'fr')
            ->shouldBeCalled()
            ->willReturn($registrationTemplateData);

        $sheetCompletenessRepository->removeForSheet($sheet)->shouldBeCalled();

        $sheetCompletenessRepository->add(new SheetCompleteness($sheet, 'fr', 100))->shouldBeCalled();
        $sheetCompletenessRepository->add(new SheetCompleteness($sheet, 'en', 100))->shouldBeCalled();

        $sheetRepository->set(Argument::that(function (Sheet $sheet) use ($expectedSheet) {
            return $sheet->getCompleteness() === $expectedSheet->getCompleteness();
        }))->shouldBeCalled();

        // Service

        $completenessCalculator = new CompletenessCalculator(
            $templateDataFactory->reveal(),
            $sheetRepository->reveal(),
            $sheetCompletenessRepository->reveal(),
            $eventDispatcher->reveal()
        );

        $completenessCalculator->calculateCompleteness($sheet);
    }

    public function testCalculateSheetCompleteness()
    {
        $locales  = ['fr', 'en'];
        $datetime = new DateTime();
        $user     = new User('user@vimeet.com', 'salt', 'password', 'fr');
        $sheet    = SheetFactory::create(null, $user, $datetime);
        $sheet->getEvent()->setLocales($locales);

        $participant = new Participant($sheet, $user, [
            '541f84d4' => [
                'text' => 'text1',
            ],
            '838197c7' => [
                'text' => 'text2',
            ],
            '1efb9cbb' => [
                'telephone' => 'telephone1',
            ],
            '3b759fbb' => [
                'telephone' => 'telephone2',
            ],
        ], true, $datetime);
        $sheet->addParticipant($participant);

        // Sheet Template Data

        $sheetTemplateData  = new TemplateData('root', [], 'fr', 'fr');
        $blockSheetTemplate = new Block('12', [], 'fr', 'fr');
        $title              = new EditableText('69b3cde2', 'editable-text', [
            'required'     => true,
            'translatable' => true,
        ], 'fr', 'fr');
        $title->setData([
            'text' => [
                'fr' => 'titre',
                'en' => '',
            ],
        ]);
        $description = new EditableText('69b3cde3', 'editable-text', [
            'required' => true,
        ], 'fr', 'fr');
        $description->setData([
            'text' => 'description',
        ]);

        $collection = new TemplateObject\ItemCollection(
            'aaa123aa',
            'collection',
            [
                'required' => true,
                'translatable' => true,
            ],
            'fr',
            'fr'
        );
        $collection->addItem(new TemplateObject\Item(new TemplateObject\ItemCollection('col123co', 'collection', [], 'fr', 'fr'), 'title_col'));
        $collection->setData([
            'items' => [
                'fr' => [
                    'title' => 'title_col',
                ],
            ],
        ]);

        $blockSheetTemplate->addChild(0, '69b3cde2', $title);
        $blockSheetTemplate->addChild(0, '69b3cde3', $description);
        $blockSheetTemplate->addChild(0, 'aaa123aaa', $collection);
        $sheetTemplateData->addChild(0, '811f6edfa', $blockSheetTemplate);

        // Registration Template Data

        $registrationTemplateData = new TemplateData('root', [], 'fr', 'fr');
        $block                    = new Block('12', [], 'fr', 'fr');
        $text                     = new TemplateObject\Text('dded0597', 'text', [], 'fr', 'fr');
        $editableText1            = new TemplateObject\EditableText('541f84d4', 'editable-text', [
            'tags'     => ['participant_firstname', 'participant_data'],
            'required' => true,
        ], 'fr', 'fr');
        $editableText2            = new TemplateObject\EditableText('838197c7', 'editable-text', [
            'tags'     => ['participant_lastname', 'participant_data'],
            'required' => true,
        ], 'fr', 'fr');
        $telephone1               = new TemplateObject\Telephone('1efb9cbb', 'telephone', [
            'tags'     => ['participant_phone', 'participant_data'],
            'required' => true,
        ], 'fr', 'fr');
        $telephone2               = new TemplateObject\Telephone('3b759fbb', 'telephone', [
            'tags'     => ['participant_mobile', 'participant_data'],
            'required' => true,
        ], 'fr', 'fr');

        $block->addChild(1, 'dded0597', $text);
        $block->addChild(1, '541f84d4', $editableText1);
        $block->addChild(1, '838197c7', $editableText2);
        $block->addChild(1, '1efb9cbb', $telephone1);
        $block->addChild(1, '3b759fbb', $telephone2);
        $registrationTemplateData->addChild(0, '811f6edf', $block);

        // Expected
        $expectedSheet = SheetFactory::create(null, $user, $datetime);
        $expectedSheet->setCompleteness(66);

        // Mock
        $templateDataFactory         = $this->prophesize(TemplateDataFactory::class);
        $sheetRepository             = $this->prophesize(SheetRepositoryInterface::class);
        $sheetCompletenessRepository = $this->prophesize(SheetCompletenessRepositoryInterface::class);
        $eventDispatcher             = $this->prophesize(DelayedEventDispatcher::class);

        $templateDataFactory->createFromSheet($sheet, 'fr')
            ->shouldBeCalled()
            ->willReturn($sheetTemplateData);

        $templateDataFactory->createRegistrationFromSheet($sheet, 'fr')
            ->shouldBeCalled()
            ->willReturn($registrationTemplateData);

        $sheetCompletenessRepository->removeForSheet($sheet)->shouldBeCalled();

        $sheetCompletenessRepository->add(new SheetCompleteness($sheet, 'fr', 100))->shouldBeCalled();
        $sheetCompletenessRepository->add(new SheetCompleteness($sheet, 'en', 33))->shouldBeCalled();

        $sheetRepository->set(Argument::that(function (Sheet $sheet) use ($expectedSheet) {
            return $sheet->getCompleteness() === $expectedSheet->getCompleteness();
        }))->shouldBeCalled();

        // Service

        $completenessCalculator = new CompletenessCalculator(
            $templateDataFactory->reveal(),
            $sheetRepository->reveal(),
            $sheetCompletenessRepository->reveal(),
            $eventDispatcher->reveal()
        );

        $completenessCalculator->calculateCompleteness($sheet);
    }

    /**
     * @deprecated
     */
    public function calculateParticipantCompleteness()
    {
        $locales  = ['fr', 'en'];
        $datetime = new DateTime();
        $user     = new User('user@vimeet.com', 'salt', 'password', 'fr');
        $sheet    = SheetFactory::create(null, $user, $datetime);
        $sheet->getEvent()->setLocales($locales);

        $participant = new Participant($sheet, $user, [
            '541f84d4' => [
                'text' => 'text1',
            ],
            '838197c7' => [
                'text' => 'text2',
            ],
            '1efb9cbb' => [
            ],
            '3b759fbb' => [
                'telephone' => 'telephone2',
            ],
        ], true, $datetime);
        $sheet->addParticipant($participant);

        // Sheet Template Data

        $sheetTemplateData  = new TemplateData('root', [], 'fr', 'fr');
        $blockSheetTemplate = new Block('12', [], 'fr', 'fr');
        $title              = new EditableText('69b3cde2', 'editable-text', [
            'required'     => true,
            'translatable' => true,
        ], 'fr', 'fr');
        $title->setData([
            'text' => [
                'fr' => 'titre',
                'en' => 'title',
            ],
        ]);
        $description = new EditableText('69b3cde3', 'editable-text', [
            'required' => true,
        ], 'fr', 'fr');
        $description->setData([
            'text' => 'description',
        ]);

        $blockSheetTemplate->addChild(0, '69b3cde2', $title);
        $blockSheetTemplate->addChild(0, '69b3cde3', $description);
        $sheetTemplateData->addChild(0, '811f6edfa', $blockSheetTemplate);

        // Registration Template Data

        $registrationTemplateData = new TemplateData('root', [], 'fr', 'fr');
        $block                    = new Block('12', [], 'fr', 'fr');
        $text                     = new TemplateObject\Text('dded0597', 'text', [], 'fr', 'fr');
        $editableText1            = new TemplateObject\EditableText('541f84d4', 'editable-text', [
            'tags'     => ['participant_firstname', 'participant_data'],
            'required' => true,
        ], 'fr', 'fr');
        $editableText2            = new TemplateObject\EditableText('838197c7', 'editable-text', [
            'tags'     => ['participant_lastname', 'participant_data'],
            'required' => true,
        ], 'fr', 'fr');
        $telephone1               = new TemplateObject\Telephone('1efb9cbb', 'telephone', [
            'tags'     => ['participant_phone', 'participant_data'],
            'required' => true,
        ], 'fr', 'fr');
        $telephone2               = new TemplateObject\Telephone('3b759fbb', 'telephone', [
            'tags'     => ['participant_mobile', 'participant_data'],
            'required' => true,
        ], 'fr', 'fr');

        $block->addChild(1, 'dded0597', $text);
        $block->addChild(1, '541f84d4', $editableText1);
        $block->addChild(1, '838197c7', $editableText2);
        $block->addChild(1, '1efb9cbb', $telephone1);
        $block->addChild(1, '3b759fbb', $telephone2);
        $registrationTemplateData->addChild(0, '811f6edf', $block);

        // Expected
        $expectedSheet = SheetFactory::create(null, $user, $datetime);
        $expectedSheet->setCompleteness(83);

        // Mock
        $templateDataFactory         = $this->prophesize(TemplateDataFactory::class);
        $sheetRepository             = $this->prophesize(SheetRepositoryInterface::class);
        $sheetCompletenessRepository = $this->prophesize(SheetCompletenessRepositoryInterface::class);
        $eventDispatcher             = $this->prophesize(DelayedEventDispatcher::class);

        $templateDataFactory->createFromSheet($sheet, 'fr')
            ->shouldBeCalled()
            ->willReturn($sheetTemplateData);

        $templateDataFactory->createRegistrationFromSheet($sheet, 'fr')
            ->shouldBeCalled()
            ->willReturn($registrationTemplateData);

        $sheetCompletenessRepository->removeForSheet($sheet)->shouldBeCalled();

        $sheetCompletenessRepository->add(new SheetCompleteness($sheet, 'fr', 83))->shouldBeCalled();
        $sheetCompletenessRepository->add(new SheetCompleteness($sheet, 'en', 83))->shouldBeCalled();

        $sheetRepository->set(Argument::that(function (Sheet $sheet) use ($expectedSheet) {
            return $sheet->getCompleteness() === $expectedSheet->getCompleteness();
        }))->shouldBeCalled();

        // Service

        $completenessCalculator = new CompletenessCalculator(
            $templateDataFactory->reveal(),
            $sheetRepository->reveal(),
            $sheetCompletenessRepository->reveal(),
            $eventDispatcher->reveal()
        );

        $completenessCalculator->calculateCompleteness($sheet);
    }
}
