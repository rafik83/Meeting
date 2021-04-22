<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Components\Home\HomeDispatchAnonymousUser;
use Proximum\Vimeet\Domain\KeyDates\Checker\RegistrationAccessChecker;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class WaitingController extends AbstractController
{
    private RegistrationAccessChecker $registrationAccessChecker;
    private TranslatorInterface $translator;

    public function __construct(
        RegistrationAccessChecker $registrationAccessChecker,
        TranslatorInterface $translator
    ) {
        $this->registrationAccessChecker = $registrationAccessChecker;
        $this->translator = $translator;
    }

    public function indexAction(EventDomain $eventDomain): Response
    {
        $event = $eventDomain->getEvent();
        $type  = $this->registrationAccessChecker->getRegistrationAccessStatus($event);

        $isLoginActivated = false;

        if (HomeDispatchAnonymousUser::TYPE_REGISTRATION_NOT_OPEN === $type) {
            $message = $this->translator->trans('event.registration_not_open');
        } elseif (HomeDispatchAnonymousUser::TYPE_REGISTRATION_CLOSED === $type) {
            $message = $this->translator->trans('event.registration_closed');
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
