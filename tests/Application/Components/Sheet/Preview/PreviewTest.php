<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Components\Sheet\Preview;

use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Components\Sheet\Preview\Preview;
use Proximum\Vimeet\Application\Components\Sheet\Preview\Resolver\ParticipantsPositionResolver;
use Proximum\Vimeet\Application\Components\Sheet\Preview\Resolver\ParticipantsResolver;
use Proximum\Vimeet\Application\Components\Sheet\Template\Tag as TemplateTag;
use Proximum\Vimeet\Application\Query\Participant\CardViewQuery;
use Proximum\Vimeet\Application\Query\Participant\CardViewQueryHandler;
use Proximum\Vimeet\Application\View\Sheet\Preview\PreviewView;
use Proximum\Vimeet\Application\View\Sheet\Preview\TagView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Template\SheetTemplate;
use Proximum\Vimeet\Domain\Rule\ComposedRule;
use Proximum\Vimeet\Domain\Template\AbstractChild;
use Proximum\Vimeet\Domain\Template\Exception\ObjectNotFoundException;
use Proximum\Vimeet\Domain\Template\TaggedDataFactory;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateObject\EditableText;
use Proximum\Vimeet\Domain\Template\TemplateObject\Participant as ParticipantObject;
use Proximum\Vimeet\Domain\Template\TemplateObject\Tag;
use Proximum\Vimeet\Domain\View\Template\TaggedDataView;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class PreviewTest extends TestCase
{
    /** @var Event */
    private $event;

    public function setUp()
    {
        $this->event = EventFactory::createEvent();
    }

    public function testGetPreview()
    {
        $locale      = 'fr';
        $sheet       = $this->prophesize(Sheet::class);
        $template    = $this->prophesize(SheetTemplate::class);
        $participant = $this->prophesize(Participant::class);
        $sheet->getEvent()->shouldBeCalled()->willReturn($this->event);
        $sheet->getTypeSheetTemplate()->shouldBeCalled()->willReturn($template->reveal());
        $sheet->getParticipants()->willReturn(new ArrayCollection([$participant->reveal()]));
        $template->getPreview()->shouldBeCalled()->willReturn([
            'key1', 'key2', 'key3', 'key4',
        ]);

        $editableText = $this->prophesize(EditableText::class);
        $editableText->getTag()->willReturn(TemplateTag::SHEET_ORGANIZATION);
        $editableText->getKey()->willReturn('key2');
        $editableText->getType()->willReturn(AbstractChild::TEMPLATE_OBJECT_TYPE_EDITABLE_TEXT);
        $editableText->isTitle()->willReturn(false);
        $editableText->getContentValue()->willReturn('content value');

        $tagObject = $this->prophesize(Tag::class);
        $tagObject->getKey()->willReturn('key3');
        $tagObject->getType()->willReturn(AbstractChild::TEMPLATE_OBJECT_TYPE_TAG);
        $taggedDataView1 = new TaggedDataView(
            AbstractChild::TEMPLATE_OBJECT_TYPE_EDITABLE_TEXT,
            true,
            ['fr' => 'test', 'en' => 'test'],
            'test',
            'TAG_1',
            false,
            null
        );
        $taggedDataView2 = new TaggedDataView(
            AbstractChild::TEMPLATE_OBJECT_TYPE_COUNTRY,
            false,
            [],
            'FR',
            'TAG_2',
            false,
            null
        );
        $tagObject->getTaggedDataViews()->willReturn([
            $taggedDataView1,
            $taggedDataView2,
        ]);
        $tagObject->getLabel($locale)->shouldBeCalled()->willReturn('Label');

        $templateData = $this->prophesize(TemplateData::class);
        $participantObject = $this->prophesize(ParticipantObject::class);
        $templateData->getObject('key1')->shouldBeCalled()->willReturn($participantObject->reveal());
        $templateData->getObject('key2')->shouldBeCalled()->willReturn($editableText->reveal());
        $templateData->getObject('key3')->shouldBeCalled()->willReturn($tagObject->reveal());
        $templateData->getObject('key4')->shouldBeCalled()->willThrow(new ObjectNotFoundException('key4'));

        $taggedDataFactory    = $this->prophesize(TaggedDataFactory::class);
        $taggedDataFactory->buildTaggedDataView($sheet->reveal(), $locale, [])
            ->shouldBeCalled()
            ->willReturn($templateData)
        ;

        $translator = $this->prophesize(TranslatorInterface::class);

        $participantsResolver = $this->prophesize(ParticipantsResolver::class);
        $participantsResolver
            ->handle($sheet->reveal(), $locale, $participantObject->reveal(), [])
            ->shouldBeCalled()
            ->willReturn(new PreviewView('key1', '', AbstractChild::TEMPLATE_OBJECT_TYPE_PARTICIPANT))
        ;

        $participantsPositionResolver = $this->prophesize(ParticipantsPositionResolver::class);
        $participantsPositionResolver->handle()->shouldNotBeCalled();

        $preview = new Preview(
            $taggedDataFactory->reveal(),
            $translator->reveal(),
            $participantsResolver->reveal(),
            $participantsPositionResolver->reveal()
        );

        $result = $preview->getPreview($sheet->reveal(), $locale);

        $previewParticipant = new PreviewView(
            'key1',
            '',
            AbstractChild::TEMPLATE_OBJECT_TYPE_PARTICIPANT
        );
        $previewText = new PreviewView(
            'key2',
            'content value',
            AbstractChild::TEMPLATE_OBJECT_TYPE_EDITABLE_TEXT
        );
        $previewText->populatedFromTag = TemplateTag::SHEET_ORGANIZATION;

        $previewTag = new PreviewView(
            'key3',
            '',
            AbstractChild::TEMPLATE_OBJECT_TYPE_TAG
        );
        $previewTag->addTagView(
            new TagView(AbstractChild::TEMPLATE_OBJECT_TYPE_EDITABLE_TEXT, 'Label', 'test', null)
        );
        $previewTag->addTagView(
            new TagView(AbstractChild::TEMPLATE_OBJECT_TYPE_COUNTRY, 'Label', 'FR', null)
        );

        $this->assertEquals([$previewParticipant, $previewText, $previewTag], $result);
    }

    public function testGetPreviewParticipantsPosition()
    {
        $locale      = 'fr';
        $sheet       = $this->prophesize(Sheet::class);
        $template    = $this->prophesize(SheetTemplate::class);
        $participant = $this->prophesize(Participant::class);

        $sheet->getEvent()->shouldBeCalled()->willReturn($this->event);
        $sheet->getTypeSheetTemplate()->shouldBeCalled()->willReturn($template->reveal());
        $sheet->getParticipants()->willReturn(new ArrayCollection([$participant->reveal()]));
        $template->getPreview()->shouldBeCalled()->willReturn(
            [
                'key1',
                'custom_preview_data_participant_position',
            ]
        );

        $editableText = $this->prophesize(EditableText::class);
        $editableText->getTag()->willReturn(null);
        $editableText->getKey()->willReturn('key1');
        $editableText->getType()->willReturn(AbstractChild::TEMPLATE_OBJECT_TYPE_EDITABLE_TEXT);
        $editableText->isTitle()->willReturn(false);
        $editableText->getContentValue()->willReturn('content value');

        $composedRule = new ComposedRule();
        $templateData = $this->prophesize(TemplateData::class);
        $templateData->getObject('key1')->shouldBeCalled()->willReturn($editableText->reveal());

        $taggedDataFactory = $this->prophesize(TaggedDataFactory::class);
        $taggedDataFactory->buildTaggedDataView($sheet->reveal(), $locale, [$composedRule->rule])
            ->shouldBeCalled()
            ->willReturn($templateData)
        ;
        $cardViewQueryHandler = $this->prophesize(CardViewQueryHandler::class);
        $cardViewQueryHandler
            ->handle(new CardViewQuery($participant->reveal(), $locale))
            ->shouldNotBeCalled()
        ;

        $translator = $this->prophesize(TranslatorInterface::class);

        $participantsPositionResolver = $this->prophesize(ParticipantsPositionResolver::class);
        $previewParticipantsPosition = new PreviewView(
            'custom_preview_data_participant_position',
            'Directeur commercial',
            'custom_preview_data_participant_position'
        );

        $participantsPositionResolver
            ->handle($sheet->reveal(), $locale)
            ->shouldBeCalled()
            ->willReturn($previewParticipantsPosition)
        ;

        $participantsResolver = $this->prophesize(ParticipantsResolver::class);
        $participantsResolver->handle()->shouldNotBeCalled();

        $preview = new Preview(
            $taggedDataFactory->reveal(),
            $translator->reveal(),
            $participantsResolver->reveal(),
            $participantsPositionResolver->reveal()
        );

        $result = $preview->getPreview($sheet->reveal(), $locale, $composedRule);

        $previewText = new PreviewView(
            'key1',
            'content value',
            AbstractChild::TEMPLATE_OBJECT_TYPE_EDITABLE_TEXT
        );

        $this->assertEquals(
            [
                $previewText,
                $previewParticipantsPosition,
            ],
            $result
        );
    }
}
