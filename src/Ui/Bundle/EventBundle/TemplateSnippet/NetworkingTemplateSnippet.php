<?php


namespace Proximum\Vimeet\Ui\Bundle\EventBundle\TemplateSnippet;


use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Query\Networking\ClosedNetworkingException;
use Proximum\Vimeet\Application\Query\Networking\GetSnippetQuery;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Symfony\Component\Templating\EngineInterface;

class NetworkingTemplateSnippet
{
    /** @var EngineInterface */
    private $engine;

    /** @var QueryBusInterface */
    private $queryBus;

    public function __construct(
        EngineInterface $engine,
        QueryBusInterface $queryBus
    )
    {
        $this->engine = $engine;
        $this->queryBus = $queryBus;
    }

    public function generate(Event $event, ?User $user) : string {

        if ($user === null) {
            return ' ';
        }

        try {
            $getSnippetView = $this->queryBus->handle(new GetSnippetQuery($event, $user));
        } catch (ClosedNetworkingException $e) {
            return ' ';
        }
        return $this->engine->render(
            '@Event/Networking/snippet.html.twig',
            [
                'getSnippetView' => $getSnippetView,
            ]
        );
    }
}
