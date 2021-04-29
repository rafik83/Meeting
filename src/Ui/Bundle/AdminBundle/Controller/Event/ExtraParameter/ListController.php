<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Event\ExtraParameter;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\Event\ExtraParameterRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class ListController extends AbstractController
{
    private ExtraParameterRepositoryInterface $extraParameterRepository;

    public function __construct(ExtraParameterRepositoryInterface $extraParameterRepository)
    {
        $this->extraParameterRepository = $extraParameterRepository;
    }

    public function listAction(Event $event): Response
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');

        $extraParameters = $this->extraParameterRepository->findByEvent($event);

        return $this->render('AdminBundle:Event/ExtraParameter:list.html.twig', [
            'event'           => $event,
            'extraParameters' => $extraParameters,
        ]);
    }
}
