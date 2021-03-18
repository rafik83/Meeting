<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\MultipleSheets;

use Proximum\Vimeet\Application\Query\Sheet\Multiple\MultipleViewQuery;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

class HomeController extends Controller
{
    /**
     * @param EventDomain   $eventDomain
     * @param UserInterface $user
     *
     * @return RedirectResponse|Response
     */
    public function selectSheetAction(EventDomain $eventDomain, UserInterface $user = null)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $event = $eventDomain->getEvent();

        $sheetViews = $this->get('tactician.commandbus.query')->handle(
            new MultipleViewQuery($user, $event)
        );

        if (count($sheetViews) <= 1) {
            return $this->redirectToRoute('event');
        }

        return $this->render('EventBundle:MultipleSheets:select-sheet.html.twig', [
            'event'=> $event,
            'sheetViews' => $sheetViews,
        ]);
    }
}
