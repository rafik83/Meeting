<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\RegistrationTemplate;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Command\Template\Registration\Duplicate;
use Proximum\Vimeet\Domain\Model\Template\RegistrationTemplate;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Security\Voter\AdminTemplateAccessVoter;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Template\Registration\DuplicateType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\ValueResolver\AdminDomain;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;

class DuplicateAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var RouterInterface */
    private $router;

    /** @var CommandBusInterface */
    private $commandBus;

    /** @var Environment */
    private $twig;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        FormFactoryInterface $formFactory,
        RouterInterface $router,
        CommandBusInterface $commandBus,
        Environment $twig
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->formFactory = $formFactory;
        $this->router = $router;
        $this->commandBus = $commandBus;
        $this->twig = $twig;
    }

    /**
     * @param Request              $request
     * @param RegistrationTemplate $template
     * @param AdminDomain          $adminDomain
     *
     * @throws AccessDeniedException
     *
     * @return RedirectResponse|Response
     */
    public function __invoke(Request $request, RegistrationTemplate $template, AdminDomain $adminDomain): Response
    {
        if (!$this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_ORGANIZE')
            || !$this->authorizationCheckerAdapter->isGranted(
                AdminTemplateAccessVoter::PERMISSION_TEMPLATE_EDIT,
                $template
            )
        ) {
            throw new AccessDeniedException('Access denied');
        }

        $duplicate = new Duplicate($template);
        $form      = $this->formFactory->create(DuplicateType::class, $duplicate, [
            'admin'  => $adminDomain->getAdmin(),
            'submit' => true,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $result = $this->commandBus->handle($duplicate);

            return new RedirectResponse($this->router->generate('admin_template_registration_build', [
                'registrationTemplate' => $result->registrationTemplate->getId(),
                'locale' => $result->registrationTemplate->getFallback(),
            ]));
        }

        return new Response($this->twig->render('AdminBundle:RegistrationTemplate:duplicate.html.twig', [
            'template' => $template,
            'form'     => $form->createView(),
        ]));
    }
}
