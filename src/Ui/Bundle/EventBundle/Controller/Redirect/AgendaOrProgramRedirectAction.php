<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Redirect;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Components\Navigation\Route;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Security\Voter\AgendaAccessVoter;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Security\Voter\HappeningAccessVoter;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class AgendaOrProgramRedirectAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var RouterInterface */
    private $router;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        RouterInterface $router
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->router = $router;
    }

    public function __invoke(
        EventDomain $eventDomain,
        Sheet $sheet
    ) {
        $event = $eventDomain->getEvent();
        if (!$this->authorizationCheckerAdapter->isGranted(SheetVoter::EDIT, $sheet)
            || $event->getId() !== $sheet->getEvent()->getId()
        ) {
            throw new AccessDeniedException('Access denied');
        }

        if ($this->authorizationCheckerAdapter->isGranted(AgendaAccessVoter::PERMISSION, $event)) {
            return new RedirectResponse(
                $this->router->generate(Route::AGENDA_DEFAULT, [
                    'sheet' => $sheet->getId()
                ])
            );
        }

        if ($this->authorizationCheckerAdapter->isGranted(HappeningAccessVoter::PERMISSION, $event)) {
            return new RedirectResponse(
                $this->router->generate(Route::PROGRAM, [
                    'sheet' => $sheet->getId()
                ])
            );
        }

        throw new AccessDeniedException('Access denied');
    }
}
