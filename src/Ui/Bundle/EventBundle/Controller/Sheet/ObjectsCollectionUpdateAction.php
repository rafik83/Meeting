<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Sheet;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Command\Sheet\UpdateData;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Template\Block;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Domain\Template\TemplateObject\ContentObjectInterface;
use Proximum\Vimeet\Domain\Template\TemplateObject\EditableText;
use Proximum\Vimeet\Domain\Template\TemplateObject\Nomenclature;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Sheet\Template\ObjectsCollectionType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Templating\EngineInterface;

class ObjectsCollectionUpdateAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationChecker;

    /** @var EngineInterface */
    private $engine;

    /** @var TemplateDataFactory */
    private $templateDataFactory;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var CommandBusInterface */
    private $commandBus;

    /** @var RouterInterface */
    private $router;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationChecker,
        EngineInterface $engine,
        TemplateDataFactory $templateDataFactory,
        FormFactoryInterface $formFactory,
        CommandBusInterface $commandBus,
        RouterInterface $router
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->engine = $engine;
        $this->templateDataFactory = $templateDataFactory;
        $this->formFactory = $formFactory;
        $this->commandBus = $commandBus;
        $this->router = $router;
    }

    public function __invoke(
        Request $request,
        EventDomain $eventDomain,
        Sheet $sheet,
        string $locale,
        string $key
    ): Response {
        if (!$this->authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED')
            || !$this->authorizationChecker->isGranted(SheetVoter::EDIT, $sheet)
        ) {
            throw new AccessDeniedException();
        }

        $event = $eventDomain->getEvent();

        $templateData = $this->templateDataFactory->createFromSheet($sheet, $locale);
        $block = $templateData->getBlockByUid($key);

        if (!$block instanceof Block || !$block->isObjectsCollection()) {
            throw new NotFoundHttpException();
        }

        $collection = [];

        $form = $this->formFactory->create(ObjectsCollectionType::class, $collection, [
            'block' => $block,
            'locale' => $locale,
            'submit' => true,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $collection = $form->getData();

            foreach ($block->getObjects() as $uid => $object) {
                $content = [];

                foreach ($collection as $collectionRow) {
                    if ($object instanceof EditableText) {
                        $content[] = $collectionRow[$uid][EditableText::CONTENT] ?? null;
                        continue;
                    }

                    if ($object instanceof Nomenclature) {
                        $content[] = $collectionRow[$uid][Nomenclature::ITEMS] ?? null;
                        continue;
                    }

                    $content[] = $collectionRow[$uid] ?? null;
                }

                if ($object instanceof ContentObjectInterface) {
                    $object->setContentValue($content);
                }
            }

            $this->commandBus->handle(new UpdateData($sheet, $templateData));

            return new RedirectResponse($this->router->generate('event_sheet_default', ['sheet' => $sheet->getId()]));
        }

        return new Response(
            $this->engine->render(
                '@Event/Sheet/objectsCollectionUpdate.html.twig',
                [
                    'event' => $event,
                    'sheet' => $sheet,
                    'locale' => $locale,
                    'key' => $key,
                    'form' => $form->createView(),
                    'label' => $block->getLabel($request->getLocale()),
                ]
            )
        );
    }
}
