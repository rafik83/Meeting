<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Translation;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\JobQueueInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event\IndexType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\ValueResolver\AdminDomain;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;

class UpdateAction
{
    /** @var JobQueueInterface */
    private $jobQueue;

    /** @var FlashBagInterface */
    private $flashBag;

    /** @var RouterInterface */
    private $router;

    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var Environment */
    private $twig;
    /** @var FormFactoryInterface */
    private $formFactory;

    public function __construct(
        JobQueueInterface $jobQueue,
        FlashBagInterface $flashBag,
        RouterInterface $router,
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        Environment $twig,
        FormFactoryInterface $formFactory
    ) {
        $this->jobQueue = $jobQueue;
        $this->flashBag = $flashBag;
        $this->router = $router;
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->twig = $twig;
        $this->formFactory = $formFactory;
    }

    public function __invoke(Request $request, AdminDomain $adminDomain): Response
    {
        if (!$this->authorizationCheckerAdapter->isGranted('ROLE_SUPER_ADMIN')) {
            throw new AccessDeniedException('Access denied');
        }

        $form = $this->formFactory
            ->createBuilder()
            ->add('submit', SubmitType::class, ['label' => 'form.translations.update.submit'])
            ->getForm()
        ;

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->jobQueue->scheduleUpdateTranslations($adminDomain->getAdmin()->getEmail(), $request->getLocale());
            $this->flashBag->add('success', 'flash.admin.translations.download.prepare');

            return new RedirectResponse($this->router->generate('admin_translations_update'));
        }

        return new Response($this->twig->render('@Admin/Translations/update.html.twig', [
            'form' => $form->createView(),
        ]));
    }
}
