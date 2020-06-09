<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Visio;

use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Components\Navigation\Route;

class EndVisioRedirectHandler
{
    /** @var RouterInterface */
    private $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function __invoke(EndVisioRedirect $endVisioRedirect): string
    {
        $type = $endVisioRedirect->sheet->getType();

        if (!$type->canEvaluateMeeting()) {
            return $this->router->generate(Route::AGENDA_PARTICIPANT, [
                'sheet' => $endVisioRedirect->sheet->getId(),
                'participant' => $endVisioRedirect->participant->getId(),
            ]);
        }

        return $this->router->generate(Route::MEETING_EVALUATION, [
            'sheet' => $endVisioRedirect->sheet->getId(),
            'meeting' => $endVisioRedirect->meeting->getId(),
        ]);
    }
}
