<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller;

use Proximum\Vimeet\Application\Command\Rule\SeeWhat;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Rule;
use Proximum\Vimeet\Domain\Model\WhoInterface;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event\WhoSeeWhoType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Rule\SeeWhatType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
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
            'submit' => true,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $rule = $this->findOrCreateRule($event, $form->get('seer')->getData(), $form->get('seeable')->getData(), $form->get('priority')->getData());

            return $this->redirectToRoute('admin_rule_see_what', ['event' => $event->getId(), 'rule' => $rule->getId()]);
        }

        $rules = $this->get('repository.rule_repository')->getByEvent($event);

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

    /**
     * @param Request $request
     * @param Event   $event
     * @param Rule    $rule
     *
     * @return RedirectResponse|Response
     */
    public function seeWhatAction(Request $request, Event $event, Rule $rule)
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
            $this->get('tactician.commandbus.default')->handle($seeWhat);

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

        $this->get('repository.rule_repository')->remove($rule);

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
            ->get('repository.rule_repository')
            ->getByEventSeerAndSeeable($event, $seer, $seeable);
    }

    /**
     * @param Event        $event
     * @param WhoInterface $seer
     * @param WhoInterface $seeable
     * @param int          $priority
     *
     * @return Rule
     */
    private function findOrCreateRule(Event $event, WhoInterface $seer, WhoInterface $seeable, $priority)
    {
        return $this->findRule($event, $seer, $seeable) ?:
            $this->get('repository.rule_repository')->add(Rule::createDefault($event, $seer, $seeable, $priority));
    }
}
