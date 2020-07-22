<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Type;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Application\Command\Type\Create;
use Proximum\Vimeet\Application\Command\Type\CreateHandler;
use Proximum\Vimeet\Application\Template\Registration\RegistrationTemplateCloner;
use Proximum\Vimeet\Application\Template\Sheet\SheetTemplateCloner;
use Proximum\Vimeet\Domain\Model\Package;
use Proximum\Vimeet\Domain\Model\Template\FormTemplate;
use Proximum\Vimeet\Domain\Model\Template\RegistrationTemplate;
use Proximum\Vimeet\Domain\Model\Template\SheetTemplate;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\TypeTranslation;
use Proximum\Vimeet\Domain\Model\ValidationCriteria;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class CreateHandlerTest extends TestCase
{
    public function testHandle()
    {
        //Context
        $event    = EventFactory::createEvent();
        $dateTime = new \DateTime();
        $package  = new Package($event, 'title', $dateTime);
        $event->setLocales(['fr'], 'fr');

        $sheetTemplate        = new SheetTemplate('base Exposant', [], ['fr'], 'fr', $dateTime);
        $registrationTemplate = new RegistrationTemplate('base Exposant', [], ['fr'], 'fr', $dateTime);
        $formTemplate1 = new FormTemplate($event, 'title1', [], ['fr'], 'fr', $dateTime);
        $formTemplate2 = new FormTemplate($event, 'title2', [], ['fr'], 'fr', $dateTime);

        //Expected
        $expectedSheetTemplate         = new SheetTemplate('Exposant', [], ['fr'], 'fr', $dateTime);
        $expectedRegistrationTemplate  = new RegistrationTemplate('Exposant', [], ['fr'], 'fr', $dateTime);
        $expectedSheetTemplate->setEvent($event);
        $expectedRegistrationTemplate->setEvent($event);

        $expectedType = new Type($event);
        $expectedType->getTranslations()->set('fr', new TypeTranslation($expectedType, 'fr', 'Exposant'));
        $expectedType->getValidationCriteria()->setSheetAccepted(true);
        $expectedType->setSheetTemplate($expectedSheetTemplate);
        $expectedType->update(null, true, true, 12, false);
        $expectedType->setRegistrationTemplate($expectedRegistrationTemplate);
        $expectedType->setPackage($package);
        $expectedType->setFormTemplates([$formTemplate1, $formTemplate2]);

        //Command
        $create = new Create($event, 'fr');
        $create->translations['fr']['title'] = 'Exposant';
        $create->validationCriteria['sheetAccepted'] = true;
        $create->sheetTemplate = $sheetTemplate;
        $create->registrationTemplate = $registrationTemplate;
        $create->package = $package;
        $create->formTemplates = [$formTemplate1, $formTemplate2];
        $create->hidden = true;
        $create->rank = null;
        $create->canMoveMeeting = false;
        $create->availabilityType = Type::TYPE_MANAGEMENT_UNAVAILABLE;
        $create->numberOfMeetingsPerPlanning = 12;
        $create->canRemoveMeeting = false;
        $create->areAllSheetParticipantsAssignedToMeeting = true;
        $create->canScanParticipant = true;
        $create->priorityMeetingRequestsNumber = 0;
        $create->numberMaxOfHappeningsPerUser = null;
        $create->canEvaluateMeeting = true;
        $create->mustEvaluateMeeting = true;
        $create->submitValidationSheet = true;

        //Mock
        $typeRepository = $this->prophesize(TypeRepositoryInterface::class);
        $typeRepository->add(Argument::that(static function (Type $actual) use ($expectedType) {
            return $expectedType->getId() === $actual->getId()
                && null === $actual->getNumberMaxOfHappeningsPerUser()
                && Type::TYPE_MANAGEMENT_UNAVAILABLE === $actual->getAvailabilityType()
                && null === $actual->getPosition()
                && true === $actual->isHidden()
                && 12 === $actual->getNumberOfMeetingsPerPlanning()
                && false === $actual->canRemoveMeeting()
                && true === $actual->areAllSheetParticipantsAssignedToMeeting()
                && 0 === $actual->getPriorityMeetingRequestsNumber()
                && 'Exposant' === $actual->getTitle('fr')
                && new ValidationCriteria(true) == $actual->getValidationCriteria()
                && $actual->canEvaluateMeeting()
                && $actual->mustEvaluateMeeting()
                && $actual->isSubmitValidationSheet()
            ;
        }))->shouldBeCalled();
        $typeRepository->typeExists($event, 'fr', 'Exposant')->shouldBeCalled()->willReturn(false);

        $sheetTemplateCloner = $this->prophesize(SheetTemplateCloner::class);
        $sheetTemplateCloner->duplicate($sheetTemplate, $event, 'Exposant')->shouldBeCalled()->willReturn($expectedSheetTemplate);

        $registrationTemplateCloner = $this->prophesize(RegistrationTemplateCloner::class);
        $registrationTemplateCloner->duplicate($registrationTemplate, $event, 'Exposant')->shouldBeCalled()->willReturn($expectedRegistrationTemplate);

        //Handler
        $handler = new CreateHandler($typeRepository->reveal(), $sheetTemplateCloner->reveal(), $registrationTemplateCloner->reveal());
        $handler->handle($create);
    }
}
