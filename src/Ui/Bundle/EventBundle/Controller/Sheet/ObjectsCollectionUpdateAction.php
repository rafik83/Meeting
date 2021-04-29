<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Sheet;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Command\Sheet\UpdateData;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Template\Block;
use Proximum\Vimeet\Domain\Template\ObjectsCollectionBlock\BlockToArray;
use Proximum\Vimeet\Domain\Template\ObjectsCollectionBlock\SetArrayContentToBlock;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Sheet\Template\ObjectsCollectionType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;

class ObjectsCollectionUpdateAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationChecker;

    /** @var Environment */
    private $twig;

    /** @var TemplateDataFactory */
    private $templateDataFactory;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var CommandBusInterface */
    private $commandBus;

    /** @var SetArrayContentToBlock */
    private $setArrayContentToBlock;

    /** @var BlockToArray */
    private $blockToArray;

    /** @var RouterInterface */
    private $router;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationChecker,
        Environment $twig,
        TemplateDataFactory $templateDataFactory,
        FormFactoryInterface $formFactory,
        CommandBusInterface $commandBus,
        BlockToArray $blockToArray,
        SetArrayContentToBlock $setArrayContentToBlock,
        RouterInterface $router
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->twig = $twig;
        $this->templateDataFactory = $templateDataFactory;
        $this->formFactory = $formFactory;
        $this->commandBus = $commandBus;
        $this->blockToArray = $blockToArray;
        $this->setArrayContentToBlock = $setArrayContentToBlock;
        $this->router = $router;
    }

    public function __invoke(
        Request $request,
        EventDomain $eventDomain,
        Sheet $sheet,
        string $key
    ): Response {
        if (!$this->authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED')
            || !$this->authorizationChecker->isGranted(SheetVoter::EDIT, $sheet)
        ) {
            throw new AccessDeniedException();
        }

        $event = $eventDomain->getEvent();
        $locale = $request->getLocale();
        $templateData = $this->templateDataFactory->createFromSheet($sheet, $locale);
        $block = $templateData->getBlockByUid($key);

        if (!$block instanceof Block || !$block->isObjectsCollection()) {
            throw new NotFoundHttpException();
        }

        $form = $this->formFactory->create(
            ObjectsCollectionType::class,
            [ObjectsCollectionType::OBJECTS_COLLECTION => ($this->blockToArray)($block)],
            [
                'block' => $block,
                'locale' => $locale,
                'locales' => $event->getLocales(),
            ]
        );

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            ($this->setArrayContentToBlock)($block, $form->getData()[ObjectsCollectionType::OBJECTS_COLLECTION]);

            $this->commandBus->handle(new UpdateData($sheet, $templateData));

            return new RedirectResponse(
                $this->router->generate('event_sheet_default', ['sheet' => $sheet->getId()])
            );
        }

        return new Response(
            $this->twig->render(
                '@Event/Sheet/objectsCollectionUpdate.html.twig',
                [
                    'event' => $event,
                    'sheet' => $sheet,
                    'locale' => $locale,
                    'key' => $key,
                    'form' => $form->createView(),
                    'label' => $block->getLabel($locale),
                ]
            )
        );
    }
}
