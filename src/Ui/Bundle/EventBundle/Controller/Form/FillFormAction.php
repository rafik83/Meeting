<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Form;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Template\FormTemplate;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Templating\EngineInterface;

class FillFormAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationChecker;

    /** @var EngineInterface */
    private $engine;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        EngineInterface $engine
    ) {
        $this->authorizationChecker = $authorizationCheckerAdapter;
        $this->engine = $engine;
    }

    public function __invoke(
        EventDomain $eventDomain,
        Sheet $sheet,
        Participant $participant,
        FormTemplate $form
    ): Response {
        if (!$this->authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED')
            || !$this->authorizationChecker->isGranted(SheetVoter::EDIT, $sheet)
        ) {
            throw new AccessDeniedException();
        }

        if (!$form->isPublished() || !$form->hasType($sheet->getType())) {
            throw new AccessDeniedException('This form is not available.');
        }

        return new Response(
            $this->engine->render(
                '@Event/Form/fillForm.html.twig',
                ['event' => $eventDomain->getEvent(), 'sheet' => $sheet]
            )
        );
    }
}
