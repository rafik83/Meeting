<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Sheet;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Query\Sheet\TemplateObjectViewQuery;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Template\Exception\ObjectNotFoundException;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Sheet\CreateObjectForm;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Sheet\CreateObjectFormHandler;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Templating\EngineInterface;

/**
 * Render the form of an object. Loaded by ajax from the sheet.
 */
class FormAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var QueryBusInterface */
    private $queryBus;

    /** @var EngineInterface */
    private $engine;

    /** @var CreateObjectFormHandler */
    private $createObjectFormHandler;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        QueryBusInterface $queryBus,
        EngineInterface $engine,
        CreateObjectFormHandler $createObjectFormHandler
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->queryBus = $queryBus;
        $this->engine = $engine;
        $this->createObjectFormHandler = $createObjectFormHandler;
    }

    public function __invoke(EventDomain $eventDomain, Sheet $sheet, $locale, $key): Response
    {
        if (!$this->authorizationCheckerAdapter->isGranted('IS_AUTHENTICATED_REMEMBERED')
            || !$this->authorizationCheckerAdapter->isGranted(SheetVoter::EDIT, $sheet)
        ) {
            throw new AccessDeniedException('Access denied');
        }

        $event = $eventDomain->getEvent();

        try {
            $templateObjectView = $this
                ->queryBus
                ->handle(new TemplateObjectViewQuery($sheet, $locale, $key));
        } catch (ObjectNotFoundException $exception) {
            throw new NotFoundHttpException($exception->getMessage());
        }

        $form = $this->createObjectFormHandler->handle(
            new CreateObjectForm($templateObjectView->templateObject, $locale, $key)
        );

        return new Response($this->engine->render('EventBundle:Sheet:form.html.twig', [
            'sheet' => $sheet,
            'uid' => $key,
            'form' => $form->createView(),
            'locale' => $locale,
            'currency' => $event->getCurrency(),
            'vatMode' => $event->getMode(),
            'label' => $templateObjectView->label,
            'templateObjectView' => $templateObjectView,
        ]));
    }
}
