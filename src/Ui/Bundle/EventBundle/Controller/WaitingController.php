<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Application\Components\Home\HomeDispatchAnonymousUser;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class WaitingController extends Controller
{
    /**
     * @param EventDomain $eventDomain
     *
     * @return Response
     */
    public function indexAction(EventDomain $eventDomain): Response
    {
        $event = $eventDomain->getEvent();
        $type  = $this
            ->get('domain.key_dates.checker.registration_access_checker')
            ->getRegistrationAccessStatus($event);

        $translator = $this->get('translator');
        $isLoginActivated = false;

        if (HomeDispatchAnonymousUser::TYPE_REGISTRATION_NOT_OPEN === $type) {
            $message = $translator->trans('event.registration_not_open');
        } elseif (HomeDispatchAnonymousUser::TYPE_REGISTRATION_CLOSED === $type) {
            $message = $translator->trans('event.registration_closed');
            $isLoginActivated = true;
        } else {
            return $this->redirectToRoute('event', ['event' => $event]);
        }

        return $this->render('EventBundle:WaitingPage:index.html.twig', [
            'event' => $event,
            'message' => $message,
            'isLoginActivated' => $isLoginActivated,
        ]);
    }
}
