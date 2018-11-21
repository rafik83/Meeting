<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Form;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Template\FormTemplate;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Symfony\Component\HttpFoundation\Request;
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
        Request $request,
        EventDomain $eventDomain,
        Sheet $sheet,
        Participant $participant,
        FormTemplate $formTemplate,
        int $step
    ): Response {
        if (!$this->authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED')
            || !$this->authorizationChecker->isGranted(SheetVoter::EDIT, $sheet)
        ) {
            throw new AccessDeniedException();
        }

        if (!$formTemplate->isPublished() || !$formTemplate->hasType($sheet->getType())) {
            throw new AccessDeniedException('This form is not available.');
        }

        $event = $eventDomain->getEvent();
        $locale = $request->getLocale();

        $formTemplateTitle = $formTemplate->getTranslatedTitle($locale);

        return new Response(
            $this->engine->render(
                '@Event/Form/fillForm.html.twig',
                [
                    'event' => $event,
                    'sheet' => $sheet,
                    'formTemplateTitle' => $formTemplateTitle,
                ]
            )
        );
    }
}
