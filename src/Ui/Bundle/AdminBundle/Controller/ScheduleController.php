<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller;

use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Command\Schedule\Configure;
use Proximum\Vimeet\Application\Query\Schedule\SlotViewQuery;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Schedule\ConfigureType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ScheduleController extends AbstractController
{
    private bool $featureEventDatesToCurrenDateEnaled;
    private QueryBusInterface $queryBus;
    private CommandBusInterface $commandBus;

    public function __construct(
        ParameterBagInterface $parameterBag,
        QueryBusInterface $queryBus,
        CommandBusInterface $commandBus
    ) {
        $this->featureEventDatesToCurrenDateEnaled = $parameterBag->get('feature_event_dates_to_current_date_enabled');
        $this->queryBus = $queryBus;
        $this->commandBus = $commandBus;
    }

    public function slotsAction(Request $request, Event $event): Response
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $command = new Configure($event);
        $form    = $this->createForm(ConfigureType::class, $command, ['submit' => true]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->commandBus->handle($command);
            $this->addFlash('success', 'flash.schedule.configure.success');

            return $this->redirectToRoute('admin_schedule_slots', ['event' => $event->getId()]);
        }

        $slots = $this->queryBus->handle(new SlotViewQuery($event));

        return $this->render('AdminBundle:Schedule:slots.html.twig', [
            'event' => $event,
            'form'  => $form->createView(),
            'slots' => $slots,
            'featureEventDatesToCurrentDateEnabled' => $this->featureEventDatesToCurrenDateEnaled,
        ]);
    }
}
