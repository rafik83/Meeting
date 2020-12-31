<?php

namespace Proximum\Vimeet\Application\Components\Sheet\Request;

use Proximum\Vimeet\Domain\Model\Meeting\Constant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;

class EnableDisableManager
{
    const ADDED_TO_CATALOG = true;

    /**
     * @var RequestRepositoryInterface
     */
    private $requestRepository;

    /**
     * Disable constructor.
     *
     * @param RequestRepositoryInterface $requestRepository
     */
    public function __construct(RequestRepositoryInterface $requestRepository)
    {
        $this->requestRepository = $requestRepository;
    }

    /**
     * @param Sheet $sheet
     * @param bool  $state
     */
    public function update(Sheet $sheet, $state)
    {
        $requests = $this->requestRepository->getAllRequestBySheet(
            $sheet,
            ['state' => Constant::FILTER_STATE_ALL]
        );

        foreach ($requests as $request) {
            /*
             * Check on meeting sheet in case of adding to catalog :
             * - If the sheet met isn't in catalog -> skip this request and do not enable request
             * - If the sheet met is in catalog    -> set disabled to false
             */
            if (self::ADDED_TO_CATALOG === $state && !$request->getSheetMet($sheet)->isInCatalog()) {
                continue;
            }

            /*
             * State depends of the catalog batch command :
             *
             * - a TRUE state is in case of adding to catalog
             * so we need to enable requests (!true === false)
             *
             * - a FALSE state is in case of removing from catalog
             * so we need to disable requests (!false === true)
             */
            $request->setDisabled(!$state);
            $this->requestRepository->update($request);
        }
    }
}
