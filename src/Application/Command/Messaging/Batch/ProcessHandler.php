<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Messaging\Batch;

use Proximum\Vimeet\Application\Adapter\EmailingSenderInterface;
use Proximum\Vimeet\Application\Command\Messaging\Campaign\ReceiverView;
use Proximum\Vimeet\Domain\Messaging\Emailing\SubstitutionResolver;
use Proximum\Vimeet\Domain\Model\BillingInfo;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\BillingInfoRepositoryInterface;

class ProcessHandler
{
    /**
     * @var EmailingSenderInterface
     */
    private $emailingSender;

    /**
     * @var SubstitutionResolver
     */
    private $substitutionResolver;

    /**
     * @var BillingInfoRepositoryInterface
     */
    private $billingInfoRepository;

    public function __construct(
        EmailingSenderInterface $emailingSender,
        SubstitutionResolver $substitutionResolver,
        BillingInfoRepositoryInterface $billingInfoRepository
    ) {
        $this->emailingSender = $emailingSender;
        $this->substitutionResolver = $substitutionResolver;
        $this->billingInfoRepository = $billingInfoRepository;
    }

    /**
     * @param Process $process
     */
    public function handle(Process $process)
    {
        $this->emailingSender->send($process->message, $this->getReceivers($process));
    }

    /**
     * @param Process $process
     *
     * @return array string => ReceiverView
     */
    private function getReceivers(Process $process)
    {
        $receivers = [];

        if ($process->message->isSendEmailToBillingInfo()) {
            $billingInfos = $this->getBillingInfosIndexedBySheetId($process->sheets);
        }

        foreach ($process->sheets as $sheet) {
            $locale = $sheet->getEvent()->getAvailableLocale($sheet->getOwnerLocale());

            // replace all placeholders by content
            $substitutions = $this->substitutionResolver->getSubstitutions($sheet, $locale);

            $email = $sheet->getOwner()->getEmail();
            $index = $email . $sheet->getId();

            if ($process->message->isSendEmailToBillingInfo() && isset($billingInfos[$sheet->getId()])) {
                $billingInfo      = $billingInfos[$sheet->getId()];
                $billingInfoIndex = $billingInfo->getEmail() . $sheet->getId();

                $receivers[$billingInfoIndex] = new ReceiverView(
                    $billingInfo->getEmail(),
                    $substitutions,
                    $locale
                );
            }

            $receivers[$index] = new ReceiverView(
                $email,
                $substitutions,
                $locale
            );
        }

        return $receivers;
    }

    /**
     * @param Sheet[] $sheets
     *
     * @return BillingInfo[] indexed by Sheet id
     */
    private function getBillingInfosIndexedBySheetId($sheets)
    {
        $billingInfos                 = $this->billingInfoRepository->getBySheets($sheets);
        $billingInfosIndexedBySheetId = [];

        foreach ($billingInfos as $billingInfo) {
            $billingInfosIndexedBySheetId[$billingInfo->getSheet()->getId()] = $billingInfo;
        }

        return $billingInfosIndexedBySheetId;
    }
}
