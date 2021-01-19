<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\User\Event\Planning;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Command\Planning\PlanningPrintFactory;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Templating\EngineInterface;

class ShowAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var EngineInterface */
    private $engine;

    /** @var PlanningPrintFactory */
    private $planningPrintFactory;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        EngineInterface $engine,
        PlanningPrintFactory $planningPrintFactory
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->engine = $engine;
        $this->planningPrintFactory = $planningPrintFactory;
    }

    public function __invoke(
        Request $request,
        Event $event,
        User $user
    ): Response {
        if (!$this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event)) {
            throw new AccessDeniedException('Access denied');
        }

        $plannings[] = $this->planningPrintFactory->getPlanningPrint($user, $event, null);

        return new Response(
            $this->engine->render(
                'AdminBundle:Planning/Print:plannings.html.twig', [
                    'plannings' => $plannings,
                ]
            )
        );
    }
}
