<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Application\Command\Participant\Add;
use Proximum\Vimeet\Application\Command\Participant\Remove;
use Proximum\Vimeet\Application\Command\Participant\RemoveResult;
use Proximum\Vimeet\Application\Exception\Participant\AlreadyLinkedToASheetOfThisEventException;
use Proximum\Vimeet\Application\Exception\Participant\CanNotRemoveAllParticipantsException;
use Proximum\Vimeet\Application\Exception\Sheet\ParticipantAlreadyExistException;
use Proximum\Vimeet\Application\Query\Package\Participant\ParticipantProductViewQuery;
use Proximum\Vimeet\Application\Query\Participant\CardListViewQuery;
use Proximum\Vimeet\Domain\Event\ContactInfoGuesser;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Template;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Participant\AddType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Participant\RemoveType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SheetParticipantController extends Controller
{
    /**
     * Render the form of the addition of a participant. Loaded by ajax from the sheet.
     *
     * @param EventDomain $eventDomain
     * @param string      $locale
     * @param string      $key
     *
     * @return Response
     * @throws \Exception
     */
    public function addParticipantAction(EventDomain $eventDomain, $locale, $key)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        $sheet = $this->getUserSheet($eventDomain->getEvent(), $locale);

        $this->denyAccessUnlessGranted(SheetVoter::EDIT, $sheet);

        if (!$sheet->canBuyParticipant()) {
            throw $this->createNotFoundException(
                sprintf('This sheet %s can not buy anymore participant', $sheet->getId())
            );
        }

        $templateData = $this->get('template.template_data_factory')->createFromSheet($sheet, $locale);

        try {
            $object = $templateData->getObject($key);
        } catch (Template\Exception\ObjectNotFoundException $exception) {
            throw $this->createNotFoundException(sprintf('The given key %s is not found', $key));
        }

        if (!$object->isParticipant()) {
            throw $this->createNotFoundException(sprintf('The given object %s is not a participant', $key));
        }

        $label = $object->getLabel($locale, $sheet->getEvent()->getFallback());

        $addParticipant = new Add($sheet, $locale, $this->getUser());
        $form           = $this->createForm(AddType::class, $addParticipant, [
            'action' => $this->generateUrl('event_sheet_handle_participant', ['locale' => $locale, 'key' => $key]),
        ]);

        $participantProductView = $this->get('tactician.commandbus.query')->handle(
            new ParticipantProductViewQuery($sheet, $locale)
        );

        return $this->render('EventBundle:Participant:add.html.twig', [
            'uid'                    => $key,
            'form'                   => $form->createView(),
            'sheet'                  => $sheet,
            'label'                  => $label,
            'participantProductView' => $participantProductView,
            'backRoute'              => 'backToSheet',
        ]);
    }

    /**
     * Add a participant and display the sheet with the modal in case of form error.
     *
     * @param Request     $request
     * @param EventDomain $eventDomain
     * @param string      $locale
     * @param string      $key
     *
     * @return Response
     * @throws \Exception
     */
    public function handleAddParticipantAction(Request $request, EventDomain $eventDomain, $locale, $key)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        $sheet = $this->getUserSheet($eventDomain->getEvent(), $locale);

        $this->denyAccessUnlessGranted(SheetVoter::EDIT, $sheet);

        if (!$sheet->canBuyParticipant()) {
            throw $this->createNotFoundException(
                sprintf('This sheet %s can not buy anymore participant', $sheet->getId())
            );
        }

        $addParticipant = new Add($sheet, $locale, $this->getUser());
        $form           = $this->createForm(AddType::class, $addParticipant, [
            'action' => $this->generateUrl('event_sheet_handle_participant', [
                'locale' => $locale,
                'key'    => $key
            ]),
        ]);

        // Handle the form, update the object and redirect to the sheet if valid
        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                $this->get('tactician.commandbus')->handle($addParticipant);

                return $this->redirectToRoute('event_sheet_locale', ['locale' => $locale]);
            } catch (AlreadyLinkedToASheetOfThisEventException $exception) {
                $form->get('email')->addError(new FormError('validators.participant.alreadyLinkedToASheet'));
            } catch (ParticipantAlreadyExistException $exception) {
                $form->get('email')->addError(new FormError('validators.participant.alreadyLinkedToThisSheet'));
            }
        }

        // If the form is not valid, render the sheet and force the popin with the participant form
        list ($nomenclatures, $participants, $taggedData) = $this->get('sheet.infos_helper')->getInfos(
            $sheet,
            $this->getUser(),
            $locale
        );
        $templateData = $this->get('template.template_data_factory')->createFromSheet($sheet, $locale);
        $object       = $this->getParticipantObject($templateData, $key);
        $label        = $object->getLabel($locale, $sheet->getEvent()->getFallback());

        $participantProductView = $this->get('tactician.commandbus.query')->handle(
            new ParticipantProductViewQuery($sheet, $locale)
        );

        return $this->render('EventBundle:Sheet:sheet.html.twig', [
            'event'                  => $eventDomain->getEvent(),
            'sheet'                  => $sheet,
            'templateData'           => $templateData,
            'locale'                 => $locale,
            'nomenclatures'          => $nomenclatures,
            'taggedData'             => $taggedData,
            'form_participant'       => $form->createView(),
            'label'                  => $label,
            'uid'                    => $key,
            'participants'           => $participants,
            'participantProductView' => $participantProductView,
        ]);
    }

    /**
     * Render the form to remove participant. Loaded by ajax from the sheet.
     *
     * @param EventDomain $eventDomain
     * @param string      $locale
     * @param string      $key
     *
     * @return Response
     * @throws \Exception
     */
    public function removeParticipantAction(EventDomain $eventDomain, $locale, $key)
    {
        list ($form, $sheet) = $this->removeParticipantData($eventDomain, $locale, $key);

        $this->denyAccessUnlessGranted(SheetVoter::EDIT, $sheet);

        $templateData = $this->get('template.template_data_factory')->createFromSheet($sheet, $locale);

        try {
            $object = $templateData->getObject($key);
        } catch (Template\Exception\ObjectNotFoundException $exception) {
            throw $this->createNotFoundException(sprintf('The given key %s is not found', $key));
        }

        if (!$object->isParticipant()) {
            throw $this->createNotFoundException(sprintf('The given object %s is not a participant', $key));
        }

        $label             = $object->getLabel($locale, $sheet->getEvent()->getFallback());
        $cardListViewQuery = new CardListViewQuery($sheet, $this->getUser(), $locale, false);
        $participants      = $this->get('tactician.commandbus.query')->handle($cardListViewQuery);

        return $this->render('EventBundle:Participant:remove.html.twig', [
            'uid'          => $key,
            'form'         => $form->createView(),
            'label'        => $label,
            'participants' => $participants,
        ]);
    }

    /**
     * Remove a participant and display the sheet with the modal in case of form error.
     *
     * @param Request     $request
     * @param EventDomain $eventDomain
     * @param string      $locale
     * @param string      $key
     *
     * @return Response
     * @throws \Exception
     */
    public function handleRemoveParticipantAction(Request $request, EventDomain $eventDomain, $locale, $key)
    {
        list ($form, $sheet, $remove) = $this->removeParticipantData($eventDomain, $locale, $key);

        $this->denyAccessUnlessGranted(SheetVoter::EDIT, $sheet);

        // Handle the form, update the object and redirect to the sheet if valid
        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                /** @var RemoveResult $result */
                $result = $this->get('tactician.commandbus')->handle($remove);

                if (!$result->hasParticipantWithMeeting()) {
                    return $this->redirectToRoute('event_sheet_locale', ['locale' => $locale]);
                } else {
                    $form->addError(
                        new FormError(
                            $this->get('translator')->transChoice(
                                'validators.participant.remove.hasMeeting',
                                $result->countParticipants(),
                                ['%participantName%' => $result->getParticipantsName(), '%contactInfo%' => ContactInfoGuesser::getContactInfos($eventDomain->getEvent())], 'validators'
                            )
                        )
                    );
                }

            } catch (CanNotRemoveAllParticipantsException $exception) {
                $form->addError(new FormError('validators.participant.canNotRemoveAllParticipants'));
            }
        }

        // If the form is not valid, render the sheet and force the popin with the remove participant form
        list ($nomenclatures, $participants, $taggedData) = $this->get('sheet.infos_helper')->getInfos(
            $sheet,
            $this->getUser(),
            $locale
        );
        $templateData = $this->get('template.template_data_factory')->createFromSheet($sheet, $locale);
        $object       = $this->getParticipantObject($templateData, $key);
        $label        = $object->getLabel($locale, $sheet->getEvent()->getFallback());

        return $this->render('EventBundle:Sheet:sheet.html.twig', [
            'event'         => $eventDomain->getEvent(),
            'sheet'         => $sheet,
            'templateData'  => $templateData,
            'locale'        => $locale,
            'nomenclatures' => $nomenclatures,
            'taggedData'    => $taggedData,
            'form_remove'   => $form->createView(),
            'label'         => $label,
            'uid'           => $key,
            'participants'  => $participants,
        ]);
    }

    /**
     * @param Event  $event
     * @param string $locale
     *
     * @return Sheet
     */
    private function getUserSheet(Event $event, $locale)
    {
        return $this->get('sheet.sheet_guesser')->getUserSheet($this->getUser(), $event, $locale);
    }

    /**
     * @param EventDomain $eventDomain
     * @param string      $locale
     * @param string      $key
     *
     * @throws \Exception
     * @return array
     */
    private function removeParticipantData($eventDomain, $locale, $key)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        $sheet = $this->getUserSheet($eventDomain->getEvent(), $locale);

        $this->denyAccessUnlessGranted(SheetVoter::EDIT, $sheet);

        if ($sheet->countParticipants() === 1) {
            throw $this->createNotFoundException('Impossible to remove participants from a sheet with one participant');
        }

        $remove = new Remove($sheet, $locale);
        $form   = $this->createForm(RemoveType::class, $remove, [
            'action' => $this->generateUrl(
                'event_sheet_handle_remove_participant',
                ['locale' => $locale, 'key' => $key]
            ),
            'participants' => $sheet->getParticipants(),
        ]);

        return [
            $form,
            $sheet,
            $remove,
        ];
    }

    /**
     * @param Template\TemplateData $templateData
     * @param string                $key
     *
     * @return Template\TemplateObject
     */
    private function getParticipantObject(Template\TemplateData $templateData, $key)
    {
        try {
            $object = $templateData->getObject($key);
        } catch (Template\Exception\ObjectNotFoundException $exception) {
            throw $this->createNotFoundException(sprintf('The given key %s is not found', $key));
        }

        if (!$object->isParticipant()) {
            throw $this->createNotFoundException(sprintf('The given object %s is not a participant', $key));
        }

        return $object;
    }
}
