<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Template\Sheet;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\Sheet\Template\Save;
use Proximum\Vimeet\Domain\Model\Template\SheetTemplate;
use Proximum\Vimeet\Domain\Template\Exception\ObjectsCollectionBlockCanNotContainForbiddenObjectsException;
use Proximum\Vimeet\Domain\Template\Exception\ObjectsCollectionBlockCanNotContainNomenclatureObjectWithDepthHigherThanOneException;
use Proximum\Vimeet\Domain\Template\Exception\ObjectsCollectionBlockCanNotContainOtherBlockException;
use Proximum\Vimeet\Infrastructure\Adapter\TranslatorAdapter;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Security\Voter\AdminTemplateAccessVoter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class SaveAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var CommandBusInterface */
    private $commandBus;

    /** @var TranslatorAdapter */
    private $translatorAdapter;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        CommandBusInterface $commandBus,
        TranslatorAdapter $translatorAdapter
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->commandBus = $commandBus;
        $this->translatorAdapter = $translatorAdapter;
    }

    public function __invoke(Request $request, SheetTemplate $template, string $locale): JsonResponse
    {
        if (!$this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_ORGANIZE')
            || !$this->authorizationCheckerAdapter->isGranted(
                AdminTemplateAccessVoter::PERMISSION_TEMPLATE_EDIT,
                $template
            )
        ) {
            throw new AccessDeniedException();
        }

        if (!$template->hasLocale($locale)) {
            return new JsonResponse(['error' => sprintf('Locale "%s" does not exist on this template', $locale)], 404);
        }

        $config = json_decode($request->getContent(), true);

        try {
            $this->commandBus->handle(new Save($template, $config));
        } catch (ObjectsCollectionBlockCanNotContainOtherBlockException $objectsCollectionBlockCanNotContainOtherBlockException) {
            return $this->getJsonErrorResponse('template.error.objectsCollectionBlockCanNotContainOtherBlock');
        } catch (ObjectsCollectionBlockCanNotContainForbiddenObjectsException $objectsCollectionBlockCanNotContainForbiddenObjectsException) {
            return $this->getJsonErrorResponse('template.error.objectsCollectionBlockCanNotContainForbiddenObjects');
        } catch (ObjectsCollectionBlockCanNotContainNomenclatureObjectWithDepthHigherThanOneException $objectsCollectionBlockCanNotContainForbiddenObjectsException) {
            return $this->getJsonErrorResponse('template.error.ObjectsCollectionBlockCanNotContainNomenclatureObjectWithDepthHigherThanOne');
        }

        return new JsonResponse($config);
    }

    private function getJsonErrorResponse(string $message, array $parameters = []): JsonResponse
    {
        return new JsonResponse(
            [
                'error' => $this->translatorAdapter->trans(
                    $message,
                    $parameters,
                    'templates'
                ),
            ],
            422
        );
    }
}
