<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\ThirdParty\Jenkins;

use Proximum\Vimeet\Application\ThirdParty\Jenkins\Command\Sheet\PrintPdfCallback;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CallbackController extends Controller
{
    /**
     * @param Request $request
     *
     * @return Response
     */
    public function printSheetBuildCallbackAction(Request $request): Response
    {
        $this->get('tactician.commandbus')->handle(
            new PrintPdfCallback(json_decode($request->getContent(), true))
        );

        return new Response();
    }
}
