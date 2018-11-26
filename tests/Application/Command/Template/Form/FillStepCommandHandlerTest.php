<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Template\Form;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Template\Form\FillStepCommand;
use Proximum\Vimeet\Application\Command\Template\Form\FillStepCommandHandler;
use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Application\View\Template\Form\BlockStepView;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Sheet\FormData as SheetFormData;
use Proximum\Vimeet\Domain\Model\Template\FormTemplate;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\User\FormData as UserFormData;
use Proximum\Vimeet\Domain\Repository\User\FormDataRepositoryInterface as UserFormDataRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Sheet\FormDataRepositoryInterface as SheetFormDataRepositoryInterface;
use Proximum\Vimeet\Domain\Template\Block;
use Proximum\Vimeet\Domain\Template\TemplateObject\Country;
use Proximum\Vimeet\Domain\Template\TemplateObject\EditableText;
use Proximum\Vimeet\Domain\Template\TemplateObject\Nomenclature;

class FillStepCommandHandlerTest extends TestCase
{
    public function testHandle()
    {
        $sheet = $this->prophesize(Sheet::class);
        $formTemplate = $this->prophesize(FormTemplate::class);
        $user = $this->prophesize(User::class);
        $participant = $this->prophesize(Participant::class);
        $participant->getUser()->shouldBeCalled()->willReturn($user->reveal());
        $sheetFormDataRepository = $this->prophesize(SheetFormDataRepositoryInterface::class);
        $userFormDataRepository = $this->prophesize(UserFormDataRepositoryInterface::class);

        $text = $this->prophesize(EditableText::class);
        $nomenclature = $this->prophesize(Nomenclature::class);
        $country = $this->prophesize(Country::class);

        $block = $this->prophesize(Block::class);
        $blockStepView = new BlockStepView($block->reveal(), 2, 4);
        $block->getEditableObjects()->shouldBeCalled()->willReturn([
            '4321' => $text->reveal(),
            '789' => $nomenclature->reveal(),
            '4567' => $country->reveal(),
        ]);


        $sheetFormDataRepository
            ->getBySheetAndFormTemplate($sheet->reveal(), $formTemplate->reveal())
            ->shouldBeCalled()
            ->willReturn(null)
        ;
        $userFormData = new UserFormData(
            $user->reveal(),
            $formTemplate->reveal(),
            [
                '1234' => ['text' => 'test'],
                '4321' => ['text' => 'toto'],
            ]
        );
        $userFormDataRepository
            ->getByUserAndFormTemplate($user->reveal(), $formTemplate->reveal())
            ->shouldBeCalled()
            ->willReturn($userFormData)
        ;

        $text->hasTag(Tag::SHEET_DATA)->shouldBeCalled()->willReturn(false);
        $text->hasTag(Tag::PARTICIPANT_DATA)->shouldBeCalled()->willReturn(true);
        $text->getData()->shouldBeCalled()->willReturn(['text' => 'titi']);

        $nomenclature->hasTag(Tag::SHEET_DATA)->shouldBeCalled()->willReturn(true);
        $nomenclature->hasTag(Tag::PARTICIPANT_DATA)->shouldBeCalled()->willReturn(false);
        $nomenclature->getData()->shouldBeCalled()->willReturn(['items' => ['azerty', 'ytreza']]);

        $country->hasTag(Tag::SHEET_DATA)->shouldBeCalled()->willReturn(true);
        $country->hasTag(Tag::PARTICIPANT_DATA)->shouldBeCalled()->willReturn(true);
        $country->getData()->shouldBeCalled()->willReturn(['country' => 'FR']);

        $userFormDataExpected = new UserFormData(
            $user->reveal(),
            $formTemplate->reveal(),
            [
                '1234' => ['text' => 'test'],
                '4321' => ['text' => 'titi'],
                '4567' => ['country' => 'FR'],
            ]
        );

        $sheetFormDataExpected = new SheetFormData(
            $sheet->reveal(),
            $formTemplate->reveal(),
            [
                '789' => ['items' => ['azerty', 'ytreza']],
                '4567' => ['country' => 'FR'],
            ]
        );

        $sheetFormDataRepository->save($sheetFormDataExpected)->shouldBeCalled();
        $userFormDataRepository->save($userFormDataExpected)->shouldBeCalled();

        $handler = new FillStepCommandHandler($userFormDataRepository->reveal(), $sheetFormDataRepository->reveal());
        $handler->handle(new FillStepCommand(
            $formTemplate->reveal(),
            $sheet->reveal(),
            $participant->reveal(),
            $blockStepView
        ));
    }
}
