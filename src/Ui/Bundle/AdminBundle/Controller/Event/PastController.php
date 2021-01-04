<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Event;

use Proximum\Vimeet\Application\Query\Event\EventListQuery;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

class PastController extends Controller
{
    /**
     * @param UserInterface $admin
     *
     * @return Response
     */
    public function listAction(UserInterface $admin): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        $events = $this
            ->get('tactician.commandbus.query')
            ->handle(new EventListQuery($admin, EventListQuery::STATE_PAST))
        ;

        return $this->render('AdminBundle:Event:past.html.twig', [
            'events' => $events,
        ]);
    }
}
