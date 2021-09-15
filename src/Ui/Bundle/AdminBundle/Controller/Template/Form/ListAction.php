<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Template\Form;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Command\Template\Form\Create;
use Proximum\Vimeet\Application\Query\Template\Form\FormTemplateListViewQuery;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Template\Form\CreateType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;

class ListAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var Environment */
    private $twig;

    /** @var QueryBusInterface */
    private $queryBus;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var CommandBusInterface */
    private $commandBus;

    /** @var RouterInterface */
    private $router;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        CommandBusInterface $commandBus,
        RouterInterface $router,
        QueryBusInterface $queryBus,
        FormFactoryInterface $formFactory,
        Environment $twig
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->queryBus = $queryBus;
        $this->formFactory = $formFactory;
        $this->twig = $twig;
        $this->commandBus = $commandBus;
        $this->router = $router;
    }

    public function __invoke(Request $request, Event $event): Response
    {
        if (!$this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_ORGANIZE')
            || !$this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event)
        ) {
            throw new AccessDeniedException('Access denied');
        }

        $create = new Create($event);
        $form = $this->formFactory->create(CreateType::class, $create, [
            'submit' => true,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->commandBus->handle($create);

            return new RedirectResponse($this->router->generate('admin_template_form_list', [
                'event' => $event->getId()
            ]));
        }

        $formTemplateListView = $this->queryBus->handle(new FormTemplateListViewQuery($event));

        return new Response($this->twig->render('AdminBundle:Template/Form:list.html.twig', [
            'event' => $event,
            'form' => $form->createView(),
            'formTemplateListView' => $formTemplateListView,
            'formSubmittedButNotValid' => $form->isSubmitted() && !$form->isValid(),
        ]));
    }
}
