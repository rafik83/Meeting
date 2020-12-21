<?php


namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Sheet;

use Proximum\Vimeet\Application\Components\Registration\StepManager;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Package\IsValidatedRequiredPackageMissing;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Transaction\IsValidatedTransactionMissing;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Router;

class SheetRedirectionMiddleware
{
    /** @var IsValidatedTransactionMissing */
    private $isValidatedTransactionMissing;

    /** @var IsValidatedRequiredPackageMissing */
    private $isValidatedRequiredPackageMissing;

    /** @var Router */
    private $router;

    /** @var ParticipantRepositoryInterface */
    private $participantRepository;

    /** @var StepManager */
    private $stepManager;

    public function __construct(
        Router $router,
        IsValidatedTransactionMissing $isValidatedTransactionMissing,
        IsValidatedRequiredPackageMissing $isValidatedRequiredPackageMissing,
        ParticipantRepositoryInterface $participantRepository,
        StepManager $stepManager
    ){
        $this->isValidatedTransactionMissing = $isValidatedTransactionMissing;
        $this->isValidatedRequiredPackageMissing = $isValidatedRequiredPackageMissing;
        $this->router = $router;
        $this->participantRepository = $participantRepository;
        $this->stepManager = $stepManager;
    }
    public function getForceRedirection(Sheet $sheet, User $user): ?RedirectResponse
    {
        $participant = $this->participantRepository->getParticipantForUserAndSheet($user, $sheet);

        if (null !== $participant) {
            $redirectStep = $this->stepManager->getRedirectStep($sheet, $participant);

            if (true === $redirectStep['redirect']) {
                return new RedirectResponse($this->router->generate(
                    $redirectStep['route'],
                    $redirectStep['parameters']
                ));
            }
        }

        if ($this->isValidatedRequiredPackageMissing->isSatisfiedBy($sheet) ||
            $this->isValidatedTransactionMissing->isSatisfiedBy($sheet)) {
            return new RedirectResponse($this->router->generate(
                'event_package_redirect_depending_on_context',
                ['sheet' => $sheet->getId()]
            ));
        }
        return null;
    }

}
