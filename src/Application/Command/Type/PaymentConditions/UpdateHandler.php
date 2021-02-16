<?php

namespace Proximum\Vimeet\Application\Command\Type\PaymentConditions;

use Proximum\Vimeet\Domain\Model\Type\PaymentConditions;
use Proximum\Vimeet\Domain\Repository\Type\PaymentConditionsRepositoryInterface;

class UpdateHandler
{
    /** @var PaymentConditionsRepositoryInterface */
    private $paymentConditionsRepository;

    /**
     * @param PaymentConditionsRepositoryInterface $paymentConditionsRepository
     */
    public function __construct(PaymentConditionsRepositoryInterface $paymentConditionsRepository)
    {
        $this->paymentConditionsRepository = $paymentConditionsRepository;
    }

    /**
     * @param Update $command
     */
    public function handle(Update $command): void
    {
        $paymentConditions = $command->type->getPaymentConditions();

        if ($paymentConditions instanceof PaymentConditions) {
            if (false === $command->specificPaymentConditions) {
                $this->paymentConditionsRepository->remove($paymentConditions);

                return;
            }

            $paymentConditions->update(
                $command->paymentModes,
                $command->allowDeposit,
                $command->depositUntil,
                $command->minimumForDeposit,
                $command->deposit
            );

            $paymentConditions->updateTranslations($command->translations);

            $this->paymentConditionsRepository->set($paymentConditions);

            return;
        }

        if (false === $command->specificPaymentConditions) {
            return;
        }

        $paymentConditions = new PaymentConditions(
            $command->type,
            $command->paymentModes,
            $command->allowDeposit,
            $command->depositUntil,
            $command->minimumForDeposit,
            $command->deposit
        );

        $paymentConditions->updateTranslations($command->translations);

        $this->paymentConditionsRepository->add($paymentConditions);
    }
}
