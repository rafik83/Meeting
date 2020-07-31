<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Sheet;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Command\Sheet\TemplateObject\Video\RemoveVideo;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Domain\Template\TemplateObject\Video;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class RemoveVideoAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var TemplateDataFactory */
    private $templateDataFactory;

    /** @var CommandBusInterface */
    private $commandBus;

    /** @var RouterInterface */
    private $router;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        TemplateDataFactory $templateDataFactory,
        CommandBusInterface $commandBus,
        RouterInterface $router
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->templateDataFactory = $templateDataFactory;
        $this->commandBus = $commandBus;
        $this->router = $router;
    }

    public function __invoke(EventDomain $eventDomain, Sheet $sheet, $locale, $key): RedirectResponse
    {
        if (!$this->authorizationCheckerAdapter->isGranted('IS_AUTHENTICATED_REMEMBERED')
            || !$this->authorizationCheckerAdapter->isGranted(SheetVoter::EDIT, $sheet)
        ) {
            throw new AccessDeniedException('Access denied');
        }

        $templateData = $this->templateDataFactory->createFromSheet($sheet, $locale);
        $object = $templateData->getObject($key);

        if (!$object instanceof Video) {
            throw new NotFoundHttpException('The key given is not a video');
        }

        $removeVideo = new RemoveVideo($object, $sheet, $templateData);
        $this->commandBus->handle($removeVideo);

        return new RedirectResponse(
            $this->router->generate('event_sheet_default', ['sheet' => $sheet->getId()])
        );
    }
}
