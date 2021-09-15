<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Template\PrintTemplate;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Domain\Model\Template\SheetTemplate;
use Proximum\Vimeet\Domain\Template\PrintTemplateResolver;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Security\Voter\AdminTemplateAccessVoter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;

class BuilderAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var PrintTemplateResolver */
    private $printTemplateResolver;

    /** @var Environment */
    private $twig;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        PrintTemplateResolver $printTemplateResolver,
        Environment $twig
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->printTemplateResolver = $printTemplateResolver;
        $this->twig = $twig;
    }

    public function __invoke(Request $request, SheetTemplate $template): Response
    {
        if (!$this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_ORGANIZE')
            || !$this->authorizationCheckerAdapter->isGranted(
                AdminTemplateAccessVoter::PERMISSION_TEMPLATE_EDIT,
                $template
            )
        ) {
            throw new AccessDeniedException('Access denied');
        }

        $resolvedPrintTemplateView = $this->printTemplateResolver->resolve($template);

        return new Response($this->twig->render('AdminBundle:SheetPrintTemplate:builder.html.twig', [
            'event' => $template->getEvent(),
            'templateId' => $template->getId(),
            'templateTitle' => $template->getTitle(),
            'locale' => $template->getAvailableLocale($request->getLocale()),
            'printValue'=> $resolvedPrintTemplateView->printValueResolved,
            'missingObjects' => $resolvedPrintTemplateView->missingObjects,
        ]));
    }
}
