<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Badge;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\User\Sheet\HasAccessToSheet;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ValueResolver\UserDomain;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;

class ScanAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationChecker;

    /** @var Environment */
    private $twig;

    /** @var HasAccessToSheet */
    private $hasAccessToSheet;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationChecker,
        Environment $twig,
        HasAccessToSheet $hasAccessToSheet
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->hasAccessToSheet = $hasAccessToSheet;
        $this->twig = $twig;
    }

    public function __invoke(
        Request $request,
        EventDomain $eventDomain,
        UserDomain $userDomain,
        Sheet $sheet
    ): Response {
        $event = $eventDomain->getEvent();
        $user = $userDomain->getUser();

        if (!$this->authorizationChecker->isGranted(SheetVoter::EDIT, $sheet)
            || !$this->hasAccessToSheet->isSatisfiedBy($user, $event, $sheet)
        ) {
            throw new AccessDeniedException();
        }

        return new Response(
            $this->twig->render(
                'EventBundle:Badge:scan.html.twig',
                [
                    'event' => $event,
                    'sheet' => $sheet,
                ]
            )
        );
    }
}
