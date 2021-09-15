<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Event;

use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Query\Event\ExtraParameter\GetExtraParameterQuery;
use Proximum\Vimeet\Domain\Event\ExtraParameter\Type;
use Proximum\Vimeet\Domain\Model\Event;
use Symfony\Component\HttpFoundation\Response;

class ExtraHeaderAction
{
    /** @var QueryBusInterface */
    private $queryBus;

    public function __construct(
        QueryBusInterface $queryBus
    ) {
        $this->queryBus = $queryBus;
    }

    public function __invoke(Event $event): Response
    {
        $trackingCodeExtraParameter = $this->queryBus->handle(
            new GetExtraParameterQuery($event, Type::TYPE_TRACKING_CODE)
        );

        return new Response(
            $trackingCodeExtraParameter instanceof Event\ExtraParameter
                ? $trackingCodeExtraParameter->getValue()
                : ''
        );
    }
}
