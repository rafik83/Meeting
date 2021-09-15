<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\ThirdParty\Jenkins;

use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\ThirdParty\Jenkins\Command\Sheet\PrintPdfCallback;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CallbackController extends AbstractController
{
    private CommandBusInterface $commandBus;

    public function __construct(
        CommandBusInterface $commandBus
    ) {
        $this->commandBus = $commandBus;
    }

    public function printSheetBuildCallbackAction(Request $request): Response
    {
        $this->commandBus->handle(
            new PrintPdfCallback(json_decode($request->getContent(), true))
        );

        return new Response();
    }
}
