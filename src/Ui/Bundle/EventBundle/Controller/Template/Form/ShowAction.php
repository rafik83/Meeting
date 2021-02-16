<?php


namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Template\Form;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Components\Sheet\SheetGuesser;
use Proximum\Vimeet\Application\Exception\Sheet\SheetNotFoundException;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Template\FormTemplate;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ValueResolver\UserDomain;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ShowAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationChecker;

    /** @var RouterInterface */
    private $router;

    /** @var SheetGuesser */
    private $sheetGuesser;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationChecker,
        RouterInterface $router,
        SheetGuesser $sheetGuesser
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->router = $router;
        $this->sheetGuesser = $sheetGuesser;
    }

    public function __invoke(
        Request $request,
        EventDomain $eventDomain,
        ?UserDomain $userDomain,
        FormTemplate $formTemplate
    ): RedirectResponse {
        if (!$this->authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw new AccessDeniedException();
        }

        $event = $eventDomain->getEvent();

        try {
            $sheet = $this->sheetGuesser->getUserSheet($userDomain->getUser(), $event, $event->getFallback());
        } catch (SheetNotFoundException $exception) {
            return new RedirectResponse($this->router->generate('event'));
        }

        $participant = $sheet->getUserParticipant($userDomain->getUser());

        if (!$participant instanceof Participant) {
            $participant = $sheet->getFirstParticipant();
        }

        return new RedirectResponse($this->router->generate('event_participant_fill_form',
            [
                'formTemplate' => $formTemplate->getId(),
                'sheet' => $sheet->getId(),
                'participant' => $participant->getId(),
                'step' => 1,
            ]
        ));
    }
}
