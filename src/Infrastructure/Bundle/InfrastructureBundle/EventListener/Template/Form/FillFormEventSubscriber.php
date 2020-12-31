<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\EventListener\Template\Form;

use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Command\User\Event\PresenceDate\Persist;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Template\Form\FilledFormStepEvent;
use Proximum\Vimeet\Application\Query\Template\Form\FormTemplateDataQuery;
use Proximum\Vimeet\Application\Command\UserEventView\Update as UpdateUserEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class FillFormEventSubscriber implements EventSubscriberInterface
{
    /** @var CommandBusInterface */
    private $commandBus;

    /** @var QueryBusInterface */
    private $queryBus;

    public function __construct(CommandBusInterface $commandBus, QueryBusInterface $queryBus)
    {
        $this->commandBus = $commandBus;
        $this->queryBus = $queryBus;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::FILLED_FORM_STEP => 'onFormStepFilled',
        ];
    }

    public function onFormStepFilled(FilledFormStepEvent $filledFormStepEvent): void
    {
        $templateData = $this->queryBus->handle(new FormTemplateDataQuery(
            $filledFormStepEvent->formTemplate,
            $filledFormStepEvent->participant->getSheet(),
            $filledFormStepEvent->participant,
            $filledFormStepEvent->participant->getLocale()
        ));

        $this->commandBus->handle(
            new Persist(
                $filledFormStepEvent->participant->getEvent(),
                $filledFormStepEvent->participant->getUser(),
                $templateData
            )
        );

        $this->commandBus->handle(
            new UpdateUserEvent(
                $filledFormStepEvent->participant->getUser(),
                $filledFormStepEvent->participant->getEvent()
            )
        );
    }
}
