<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Contact;

use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Query\Contact\ContactPreviewView;
use Proximum\Vimeet\Application\Query\Contact\GetContactListViewQuery;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ValueResolver\UserDomain;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Templating\EngineInterface;

class IndexAction
{
    /** @var EngineInterface */
    private $engine;

    /**
     * @var QueryBusInterface
     */
    private $queryBus;

    public function __construct(
        QueryBusInterface $queryBus,
        EngineInterface $engine
    ) {
        $this->engine = $engine;
        $this->queryBus = $queryBus;
    }

    public function __invoke(
        Request $request,
        EventDomain $eventDomain,
        UserDomain $userDomain,
        Sheet $sheet
    ) {
        /** @var ContactPreviewView[] $contactListView */
        $contactListView = $this->queryBus->handle(
            new GetContactListViewQuery($eventDomain->getEvent(), $userDomain->getUser(), $request->getLocale())
        );

        return new Response(
            $this->engine->render(
                '@Event/Contact/index.html.twig',
                ['contactListView' => $contactListView, 'sheet' => $sheet, 'event' => $eventDomain->getEvent()]
            )
        );
    }
}
