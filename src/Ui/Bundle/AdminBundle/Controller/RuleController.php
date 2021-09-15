<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller;

use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\Rule\SeeWhat;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Rule;
use Proximum\Vimeet\Domain\Model\WhoInterface;
use Proximum\Vimeet\Domain\Repository\RuleRepositoryInterface;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event\WhoSeeWhoType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Rule\SeeWhatType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RuleController extends AbstractController
{
    private RuleRepositoryInterface $ruleRepository;
    private CommandBusInterface $commandBus;

    public function __construct(
        RuleRepositoryInterface $ruleRepository,
        CommandBusInterface $commandBus
    ) {
        $this->ruleRepository = $ruleRepository;
        $this->commandBus = $commandBus;
    }

    public function listAction(Request $request, Event $event): Response
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $locale = $event->getAvailableLocale($request->getLocale());

        $form = $this->createForm(WhoSeeWhoType::class, [], [
            'action' => $this->generateUrl('admin_rule_list', ['event' => $event->getId()]),
            'method' => 'POST',
            'event'  => $event,
            'locale' => $locale,
            'submit' => true,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $rule = $this->findOrCreateRule($event, $form->get('seer')->getData(), $form->get('seeable')->getData(), $form->get('priority')->getData());

            return $this->redirectToRoute('admin_rule_see_what', ['event' => $event->getId(), 'rule' => $rule->getId()]);
        }

        $rules = $this->ruleRepository->getByEvent($event);

        // Order rules by priority asc
        usort($rules, function (Rule $rule, Rule $anotherRule) {
            return $rule->getPriority() > $anotherRule->getPriority();
        });

        return $this->render('AdminBundle:Rule:list.html.twig', [
            'form'   => $form->createView(),
            'event'  => $event,
            'rules'  => $rules,
            'locale' => $locale,
        ]);
    }

    public function seeWhatAction(Request $request, Event $event, Rule $rule): Response
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        if ($rule->getEvent() !== $event) {
            throw $this->createNotFoundException(
                sprintf(
                    'This rule %s is not on this event %s',
                    $rule->getId(),
                    $event->getId()
                )
            );
        }

        $seeWhat = new SeeWhat($rule);
        $form    = $this->createForm(SeeWhatType::class, $seeWhat, [
            'rule'   => $rule,
            'locale' => $event->getAvailableLocale($request->getLocale()),
            'submit' => true,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->commandBus->handle($seeWhat);

            $this->addFlash('success', 'flash.admin.event.who_see_what.success');

            return $this->redirectToRoute('admin_rule_list', ['event' => $event->getId()]);
        }

        return $this->render('AdminBundle:Rule:seeWhat.html.twig', [
            'form'    => $form->createView(),
            'event'   => $event,
            'seer'    => $rule->getSeer(),
            'seeable' => $rule->getSeeable(),
            'locale'  => $event->getAvailableLocale($request->getLocale()),
        ]);
    }

    public function deleteAction(Event $event, Rule $rule): RedirectResponse
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        if ($rule->getEvent() !== $event) {
            throw $this->createNotFoundException('Rule not found');
        }

        $this->ruleRepository->remove($rule);

        return $this->redirectToRoute('admin_rule_list', ['event' => $event->getId()]);
    }

    private function findRule(Event $event, WhoInterface $seer, WhoInterface $seeable): ?Rule
    {
        return $this->ruleRepository
            ->getByEventSeerAndSeeable($event, $seer, $seeable);
    }

    private function findOrCreateRule(Event $event, WhoInterface $seer, WhoInterface $seeable, int $priority): Rule
    {
        return $this->findRule($event, $seer, $seeable) ?:
            $this->ruleRepository->add(Rule::createDefault($event, $seer, $seeable, $priority));
    }
}
