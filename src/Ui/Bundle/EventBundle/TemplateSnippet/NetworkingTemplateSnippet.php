<?php


namespace Proximum\Vimeet\Ui\Bundle\EventBundle\TemplateSnippet;


use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Query\Networking\ClosedNetworkingException;
use Proximum\Vimeet\Application\Query\Networking\GetSnippetQuery;
use Proximum\Vimeet\Domain\Model\Sheet;
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
    ) {
        $this->engine = $engine;
        $this->queryBus = $queryBus;
    }

    public function generate(Sheet $sheet, ?User $user): string
    {

        if ($user === null) {
            return ' ';
        }

        if ($sheet === null) {
            return ' ';
        }

        try {
            $getSnippetView = $this->queryBus->handle(new GetSnippetQuery($sheet, $user));
        } catch (ClosedNetworkingException $e) {
            return ' ';
        }

        $template = $this->engine->render(
            '@Event/Networking/snippet.html.twig',
            [
                'getSnippetView' => $getSnippetView,
            ]
        );

        return $template;
    }
}
