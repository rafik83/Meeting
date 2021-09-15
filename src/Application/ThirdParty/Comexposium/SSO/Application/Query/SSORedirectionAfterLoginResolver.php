<?php

namespace Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\Application\Query;

use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class SSORedirectionAfterLoginResolver
{
    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var SSORegistrationTypeResolver */
    private $SSORegistrationTypeResolver;

    /** @var RouterInterface */
    private $router;

    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        SSORegistrationTypeResolver $SSORegistrationTypeResolver,
        RouterInterface $router
    ) {
        $this->sheetRepository = $sheetRepository;
        $this->SSORegistrationTypeResolver = $SSORegistrationTypeResolver;
        $this->router = $router;
    }

    public function handle(Event $event, User $user): ?string
    {
        $type = $this->SSORegistrationTypeResolver->handle($event);

        if (!$type instanceof Type) {
            return null;
        }

        $sheets = $this->sheetRepository->getSheetsByUserAndEvent($user, $event);

        if (!empty($sheets)) {
            return null;
        }

        return $this->router->generate('event_participate', ['typeView' => $type->getId()]);
    }
}
