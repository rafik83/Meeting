<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Template\Form;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\Template\Form\Save;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Template\FormTemplate;
use Proximum\Vimeet\Domain\Template\Exception\TemplateException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class SaveAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var CommandBusInterface */
    private $commandBus;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        CommandBusInterface $commandBus
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->commandBus = $commandBus;
    }

    public function __invoke(Request $request, Event $event, FormTemplate $template, string $locale): JsonResponse
    {
        if (!$this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_ORGANIZE')
            || $template->getEvent() !== $event
            || !$this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event)
        ) {
            throw new AccessDeniedException('Access denied!');
        }

        if (!$template->hasLocale($locale)) {
            return new JsonResponse(['error' => sprintf('Locale "%s" does not exist for this template', $locale)], 404);
        }

        $config = json_decode($request->getContent(), true);

        try {
            $this->commandBus->handle(new Save($template, $config));
        } catch (TemplateException $registrationTemplateException) {
            return new JsonResponse(
                ['error' => $registrationTemplateException->getMessage()],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        return new JsonResponse($config);
    }
}
