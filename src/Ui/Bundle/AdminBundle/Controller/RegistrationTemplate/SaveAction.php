<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\RegistrationTemplate;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\Template\Registration\Save;
use Proximum\Vimeet\Domain\Model\Template\RegistrationTemplate;
use Proximum\Vimeet\Domain\Template\Exception\RegistrationTemplateException;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Security\Voter\AdminTemplateAccessVoter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class SaveAction
{
    private AuthorizationCheckerAdapterInterface $authorizationChecker;
    private CommandBusInterface $commandBus;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationChecker,
        CommandBusInterface $commandBus
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->commandBus = $commandBus;
    }

    public function __invoke(Request $request, RegistrationTemplate $registrationTemplate, string $locale): JsonResponse
    {
        if (!$this->authorizationChecker->isGranted('ROLE_ALLOWED_TO_ORGANIZE')
            || !$this->authorizationChecker->isGranted(
                AdminTemplateAccessVoter::PERMISSION_TEMPLATE_EDIT,
                $registrationTemplate
            )
        ) {
            throw new AccessDeniedException();
        }

        if (!$registrationTemplate->hasLocale($locale)) {
            return new JsonResponse(['error' => sprintf('Locale "%s" does not exist for this template', $locale)], 404);
        }

        $config = json_decode($request->getContent(), true);

        try {
            $this->commandBus->handle(new Save($registrationTemplate, $config));
        } catch (RegistrationTemplateException $registrationTemplateException) {
            return new JsonResponse(
                ['error' => $registrationTemplateException->getMessage()],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        return new JsonResponse($config);
    }
}
