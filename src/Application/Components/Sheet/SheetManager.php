<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Sheet;

use Proximum\Vimeet\Application\Components\Sheet\Apply\Applier;
use Proximum\Vimeet\Application\Components\Sheet\Apply\Strategy\SetNullStrategy;
use Proximum\Vimeet\Domain\Model\See;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\SheetDataView;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\NomenclatureItemRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SeeRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;

class SheetManager
{
    /**
     * @var NomenclatureItemRepositoryInterface
     */
    private $nomenclatureItemRepository;

    /**
     * @var SeeRepositoryInterface
     */
    private $seeRepository;

    /**
     * @var TypeRepositoryInterface
     */
    private $typeRepository;

    /**
     * SheetManager constructor.
     *
     * @param NomenclatureItemRepositoryInterface $nomenclatureItemRepository
     * @param SeeRepositoryInterface              $seeRepository
     * @param TypeRepositoryInterface             $typeRepository
     */
    public function __construct(
        NomenclatureItemRepositoryInterface $nomenclatureItemRepository,
        SeeRepositoryInterface $seeRepository,
        TypeRepositoryInterface $typeRepository
    ) {
        $this->nomenclatureItemRepository = $nomenclatureItemRepository;
        $this->seeRepository              = $seeRepository;
        $this->typeRepository             = $typeRepository;
    }

    /**
     * @param Sheet  $sheet
     * @param string $locale
     *
     * @return SheetDataView
     */
    public function getSheetDataView(Sheet $sheet, $locale)
    {
        return new SheetDataView(
            $sheet->getId(),
            $sheet->getEvent(),
            $sheet->getType(),
            $sheet->getParticipants(),
            $this->getData($sheet, $locale),
            $sheet->getPackageData(),
            $sheet->getBillingData()
        );
    }

    /**
     * @param Sheet  $sheet
     * @param string $locale
     *
     * @return mixed
     */
    private function getData(Sheet $sheet, $locale)
    {
        $sheetTemplate = $sheet->getTypeSheetTemplate();
        $data          = $sheet->getData();

        foreach ($sheetTemplate as $keyBlock => $block) {
            if (isset($block['template'])) {
                foreach ($block['template'] as $keyField => $field) {
                    if (isset($field['type'])
                        && 'lib_nomenclature' === $field['type']
                        && isset($data[$keyBlock][$keyField]['value'])
                    ) {
                        $data[$keyBlock][$keyField]['label'] = $this
                            ->nomenclatureItemRepository
                            ->getNomenclatureItemLabelById($data[$keyBlock][$keyField]['value'], $locale);
                    }
                }
            }
        }

        return $data;
    }

    /**
     * Get data seeable by $user
     *
     * @param Sheet $sheet
     * @param User  $user
     */
    public function apply(Sheet $sheet, User $user)
    {
        $applier = new Applier();
        $applier->apply($this->getSeeToApply($sheet, $user), $sheet, new SetNullStrategy());
    }

    /**
     * @param Sheet $sheet
     * @param User  $user
     *
     * @return See
     */
    private function getSeeToApply(Sheet $sheet, User $user)
    {
        $types = $this->typeRepository->getTypesByUser($user);
        $sees  = [];

        foreach ($types as $type) {
            $sees[] = $this->seeRepository->getBySeerTypeAndSeeableType($type, $sheet->getType());
        }

        return $sees[0];
    }
}
