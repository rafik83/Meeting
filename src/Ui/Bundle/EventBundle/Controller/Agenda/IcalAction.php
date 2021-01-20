<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Agenda;

use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Query\Agenda\AgendaViewQuery;
use Proximum\Vimeet\Application\View\Agenda\AgendaView;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Agenda\IcalHandler;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\UriSigner;

class IcalAction
{
    private QueryBusInterface $queryBus;
    private IcalHandler $icalHandler;
    private UriSigner $uriSigner;

    public function __construct(QueryBusInterface $queryBus, IcalHandler $icalHandler, UriSigner $uriSigner)
    {
        $this->queryBus = $queryBus;
        $this->icalHandler = $icalHandler;
        $this->uriSigner = $uriSigner;
    }

    public function __invoke(
        EventDomain $eventDomain,
        Request $request,
        Participant $participant,
        Sheet $sheet
    ): Response {
        if (!$this->uriSigner->check($request->getUri())) {
            throw new AccessDeniedHttpException('URL must be correctly signed');
        }

        if ($participant->getSheet() !== $sheet) {
            throw new NotFoundHttpException('This participant is not in this sheet');
        }

        $agendaView = $this->queryBus->handle(new AgendaViewQuery(
            $eventDomain->getEvent(),
            $sheet,
            $participant,
            $request->getLocale(),
            $participant->getUser()
        ));


        return $this->icalHandler->handle($agendaView);
    }
}
