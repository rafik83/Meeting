<?php


namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Happening;


use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Components\Navigation\Route;

class EndHappeningRedirectHandler
{
    /** @var RouterInterface */
    private $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function __invoke(EndHappeningRedirect $endHappeningRedirect): string
    {
        $type = $endHappeningRedirect->sheet->getType();

        if (!$type->canEvaluateHappening()) {
            return $this->router->generate(Route::PROGRAM, [
                'sheet' => $endHappeningRedirect->sheet->getId()
            ]);
        }

        return $this->router->generate(Route::HAPPENING_EVALUATION, [
            'sheet' => $endHappeningRedirect->sheet->getId(),
            'happening' => $endHappeningRedirect->happening->getId()
        ]);
    }
}
