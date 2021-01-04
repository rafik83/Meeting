<?php

namespace Domain\Account;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Account\EventParticipationPreFiller;
use Proximum\Vimeet\Domain\Template\Block;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Domain\Template\TemplateObject;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\ParticipantFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;
use Proximum\Vimeet\Tests\Factory\UserFactory;

class EventParticipationPreFillerTest extends TestCase
{
    public function testPreFillTemplateByTags()
    {
        $event = EventFactory::createEvent();
        $user = UserFactory::create();
        $sheet = SheetFactory::create($event, $user);
        $participant = ParticipantFactory::create($sheet, $user);
        $locale = 'fr';

        $templateData = $this->getPreviousTemplate();
        $currentTemplateData = $this->getCurrentTemplate();

        $templateDataFactory = $this->prophesize(TemplateDataFactory::class);

        $templateDataFactory
            ->createRegistrationFromParticipant($participant, $locale)
            ->shouldBeCalled()->willReturn($templateData);

        $eventParticipationPreFiller = new EventParticipationPreFiller($templateDataFactory->reveal());

        $result = $eventParticipationPreFiller->preFillTemplate(
            $currentTemplateData,
            $participant,
            $locale
        );

        $this->assertEquals($templateData, $result);
    }

    public function testPreFillTemplateByKey()
    {
        $event = EventFactory::createEvent();
        $user = UserFactory::create();
        $sheet = SheetFactory::create($event, $user);
        $participant = ParticipantFactory::create($sheet, $user);
        $locale = 'fr';

        $currentTemplateData = new TemplateData('root', [], 'fr', 'fr');

        $currentBlock = new Block('12', [], 'fr', 'fr');
        $currentEditableText1 = new TemplateObject\EditableText('69b3cde1', 'editable-text', [], 'fr', 'fr');

        $currentBlock->addChild(1, '69b3cde1', $currentEditableText1);
        $currentTemplateData->addChild(0, '811f6edf', $currentBlock);

        $templateData = new TemplateData('root', [], 'fr', 'fr');

        $block = new Block('12', [], 'fr', 'fr');
        $editableText1 = new TemplateObject\EditableText('69b3cde1', 'editable-text', [], 'fr', 'fr');
        $editableText1->setData(['text' => 'Elao Paris']);

        $block->addChild(1, '69b3cde1', $editableText1);
        $templateData->addChild(0, '811f6edf', $block);

        $templateDataFactory = $this->prophesize(TemplateDataFactory::class);

        $templateDataFactory
            ->createRegistrationFromParticipant($participant, $locale)
            ->shouldBeCalled()->willReturn($templateData);

        $eventParticipationPreFiller = new EventParticipationPreFiller($templateDataFactory->reveal());

        $result = $eventParticipationPreFiller->preFillTemplate(
            $currentTemplateData,
            $participant,
            $locale
        );

        $this->assertEquals($templateData, $result);
    }

    /**
     * @return TemplateData
     */
    private function getPreviousTemplate(): TemplateData
    {
        $templateData = new TemplateData('root', [], 'fr', 'fr');

        $block = new Block('12', [], 'fr', 'fr');

        $editableText1 = new TemplateObject\EditableText('69b3cde1', 'editable-text', [
            'tags' => ['participant_firstname', 'participant_data'],
        ], 'fr', 'fr');
        $editableText1->setData(['text' => 'vincent']);
        $editableText2 = new TemplateObject\EditableText('69b3cde2', 'editable-text', [
            'tags' => ['participant_lastname', 'participant_data', 'sheet_data'],
        ], 'fr', 'fr');
        $editableText2->setData(['text' => 'dupond']);
        $telephone1 = new TemplateObject\Telephone('69b3cde1', 'telephone', [
            'tags' => ['participant_phone', 'participant_data'],
        ], 'fr', 'fr');
        $telephone1->setData(['telephone' => '+33010000000']);
        $telephone2 = new TemplateObject\Telephone('69b3cde2', 'telephone', [
            'tags' => ['participant_mobile', 'participant_data'],
        ], 'fr', 'fr');
        $telephone2->setData(['telephone' => '+33600000000']);

        $block->addChild(1, '541f84d4', $editableText1);
        $block->addChild(1, '838197c7', $editableText2);
        $block->addChild(1, '1efb9cbb', $telephone1);
        $block->addChild(1, '3b759fbb', $telephone2);
        $templateData->addChild(0, '811f6edf', $block);

        return $templateData;
    }

    /**
     * @return TemplateData
     */
    private function getCurrentTemplate(): TemplateData
    {
        $currentTemplateData = new TemplateData('root', [], 'fr', 'fr');

        $currentBlock = new Block('12', [], 'fr', 'fr');

        $currentEditableText1 = new TemplateObject\EditableText('69b3cde1', 'editable-text', [
            'tags' => ['participant_firstname', 'participant_data'],
        ], 'fr', 'fr');
        $currentEditableText2 = new TemplateObject\EditableText('69b3cde2', 'editable-text', [
            'tags' => ['participant_lastname', 'participant_data', 'sheet_data'],
        ], 'fr', 'fr');
        $currentTelephone1 = new TemplateObject\Telephone('69b3cde1', 'telephone', [
            'tags' => ['participant_phone', 'participant_data'],
        ], 'fr', 'fr');
        $currentTelephone2 = new TemplateObject\Telephone('69b3cde2', 'telephone', [
            'tags' => ['participant_mobile', 'participant_data'],
        ], 'fr', 'fr');

        $currentBlock->addChild(1, '541f84d4', $currentEditableText1);
        $currentBlock->addChild(1, '838197c7', $currentEditableText2);
        $currentBlock->addChild(1, '1efb9cbb', $currentTelephone1);
        $currentBlock->addChild(1, '3b759fbb', $currentTelephone2);
        $currentTemplateData->addChild(0, '811f6edf', $currentBlock);

        return $currentTemplateData;
    }
}
