<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Participant;

use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Domain\Account\Synchronizer;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class UpdateCompanyHandler
{
    /**
     * @var Synchronizer
     */
    private $accountSynchronizer;

    /**
     * @var SheetRepositoryInterface
     */
    private $sheetRepository;

    /**
     * @param SheetRepositoryInterface $sheetRepository
     * @param Synchronizer             $accountSynchronizer
     */
    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        Synchronizer $accountSynchronizer
    ) {
        $this->sheetRepository     = $sheetRepository;
        $this->accountSynchronizer = $accountSynchronizer;
    }

    /**
     * @param UpdateCompany $updateCompany
     */
    public function handle(UpdateCompany $updateCompany)
    {
        $participant  = $updateCompany->participant;
        $companyData  = $updateCompany->participant->getSheet()->getRegistrationData();
        $templateData = $updateCompany->templateData;

        foreach ($updateCompany->data as $key => $value) {
            if ($templateData->getObject($key)->hasTag(Tag::SHEET_DATA)) {
                $companyData = array_merge($companyData, [$key => $value]);

                // Set the data on the TemplateData to use it with the accountSynchronizer
                $templateData->getObject($key)->setData($value);
            }
        }

        $participant->getSheet()->setRegistrationData($companyData);

        $this->sheetRepository->set($participant->getSheet());

        if ($participant->getUser() === $updateCompany->user) {
            $this->accountSynchronizer->set($templateData, $updateCompany->participant->getUser());
        }
    }
}
