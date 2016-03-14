<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Controller\Admin;

use Proximum\Vimeet\Bundle\AppBundle\Form\Type\Event\DontSeeWhatType;
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\Event\WhoSeeWhoType;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Rule;
use Proximum\Vimeet\Domain\Model\WhoInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RuleController extends Controller
{
    /**
     * @param Request $request
     * @param Event   $event
     *
     * @return RedirectResponse|Response
     */
    public function listAction(Request $request, Event $event)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $locale = $event->getAvailableLocale($request->getLocale());

        $form = $this->createForm(WhoSeeWhoType::class, [], [
            'action' => $this->generateUrl('admin_rule_list', ['event' => $event->getId()]),
            'method' => 'POST',
            'event'  => $event,
            'locale' => $locale,
        ]);
        $form->add('submit', SubmitType::class);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $rule = $this->findOrCreateRule($event, $form->get('seer')->getData(), $form->get('seeable')->getData());

            return $this->redirect($this->generateWhatUrl($rule));
        }

        $rules = $this->get('vimeet_infrastructure.repository.rule_repository')->getByEvent($event);

        return $this->render('VimeetAppBundle:Admin/Rule:list.html.twig', [
            'form'   => $form->createView(),
            'event'  => $event,
            'rules'  => $rules,
            'locale' => $locale,
        ]);
    }

    /**
     * @param Request $request
     * @param Event   $event
     * @param string  $seerIdentifier
     * @param int     $seerId
     * @param string  $seeableIdentifier
     * @param int     $seeableId
     *
     * @return RedirectResponse|Response
     */
    public function whatAction(Request $request, Event $event, $seerIdentifier, $seerId, $seeableIdentifier, $seeableId)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $locale = $event->getAvailableLocale($request->getLocale());

        $seer = $this->findWho($seerIdentifier, $seerId);
        $this->notFoundUnless($seer, 'Seer not found.');

        $seeable = $this->findWho($seeableIdentifier, $seeableId);
        $this->notFoundUnless($seeable, 'Seeable not found.');

        $rule = $this->findRule($event, $seer, $seeable);
        $this->notFoundUnless($rule, 'Rule not found.');

        $form = $this->createWhatForm($rule, $locale);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $rule->setWhat($form->getData());
            $this->get('vimeet_infrastructure.repository.rule_repository')->set($rule);
            $this->addFlash('success', 'flash.admin.event.who_see_what.success');

            return $this->redirectToRoute('admin_rule_list', ['event' => $event->getId()]);
        }

        return $this->render('VimeetAppBundle:Admin/Rule:what.html.twig', [
            'form'    => $form->createView(),
            'event'   => $event,
            'seer'    => $seer,
            'seeable' => $seeable,
            'locale'  => $locale,
        ]);
    }

    /**
     * @param Event $event
     * @param Rule  $rule
     *
     * @return RedirectResponse
     */
    public function deleteAction(Event $event, Rule $rule)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        if ($rule->getEvent() !== $event) {
            throw $this->createNotFoundException('Rule not found');
        }

        $this->get('vimeet_infrastructure.repository.rule_repository')->remove($rule);

        return $this->redirectToRoute('admin_rule_list', ['event' => $event->getId()]);
    }

    /**
     * @param Event        $event
     * @param WhoInterface $seer
     * @param WhoInterface $seeable
     *
     * @return Rule|null
     */
    private function findRule(Event $event, WhoInterface $seer, WhoInterface $seeable)
    {
        return $this
            ->get('vimeet_infrastructure.repository.rule_repository')
            ->getByEventSeerAndSeeable($event, $seer, $seeable);
    }

    /**
     * @param Event        $event
     * @param WhoInterface $seer
     * @param WhoInterface $seeable
     *
     * @return Rule
     */
    private function findOrCreateRule(Event $event, WhoInterface $seer, WhoInterface $seeable)
    {
        return $this->findRule($event, $seer, $seeable) ?:
            $this->get('vimeet_infrastructure.repository.rule_repository')->add(new Rule($event, $seer, $seeable, []));
    }

    /**
     * @param $identifier
     * @param $id
     *
     * @return WhoInterface
     */
    private function findWho($identifier, $id)
    {
        return $this
            ->getDoctrine()
            ->getRepository(sprintf('Entity:%s', ucfirst($identifier)))
            ->find($id);
    }

    /**
     * @param Rule   $rule
     * @param string $locale
     *
     * @return Form
     */
    private function createWhatForm(Rule $rule, $locale)
    {
        $form = $this->createForm(DontSeeWhatType::class, $rule->getWhat(), [
            'action' => $this->generateWhatUrl($rule),
            'method' => 'POST',
            'who'    => $rule->getSeeable(),
            'locale' => $locale,
        ]);
        $form->add('submit', SubmitType::class);

        return $form;
    }

    /**
     * @param Rule $rule
     *
     * @return string
     */
    private function generateWhatUrl(Rule $rule)
    {
        return $this->generateUrl('admin_who_see_who_dont_see_what', [
            'event'             => $rule->getEvent()->getId(),
            'seerIdentifier'    => $rule->getSeer()->getIdentifier(),
            'seerId'            => $rule->getSeer()->getId(),
            'seeableIdentifier' => $rule->getSeeable()->getIdentifier(),
            'seeableId'         => $rule->getSeeable()->getId(),
        ]);
    }

    /**
     * @param mixed  $condition
     * @param string $message
     */
    private function notFoundUnless($condition, $message = 'Not found.')
    {
        if (!$condition) {
            throw $this->createNotFoundException($message);
        }
    }
}
