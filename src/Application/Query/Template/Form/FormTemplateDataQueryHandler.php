<?php

namespace Proximum\Vimeet\Application\Query\Template\Form;

use Proximum\Vimeet\Domain\Template\TemplateData;

use Proximum\Vimeet\Domain\Model\Sheet\FormData as SheetFormData;
use Proximum\Vimeet\Domain\Model\User\FormData as UserFormData;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Domain\Repository\Sheet as SheetRepository;
use Proximum\Vimeet\Domain\Repository\User as UserRepository;

class FormTemplateDataQueryHandler
{
    /** @var TemplateDataFactory */
    private $templateDataFactory;

    /** @var SheetRepository\FormDataRepositoryInterface */
    private $sheetFormDataRepository;

    /** @var UserRepository\FormDataRepositoryInterface */
    private $userFormDataRepository;

    public function __construct(
        TemplateDataFactory $templateDataFactory,
        SheetRepository\FormDataRepositoryInterface $sheetFormDataRepository,
        UserRepository\FormDataRepositoryInterface $userFormDataRepository
    ) {
        $this->templateDataFactory = $templateDataFactory;
        $this->sheetFormDataRepository = $sheetFormDataRepository;
        $this->userFormDataRepository = $userFormDataRepository;
    }

    public function handle(FormTemplateDataQuery $query): TemplateData
    {
        $sheetFormData = $this->sheetFormDataRepository->getBySheetAndFormTemplate($query->sheet, $query->formTemplate);
        $userFormData = $this->userFormDataRepository->getByUserAndFormTemplate($query->participant->getUser(), $query->formTemplate);
        $data = [];

        if ($sheetFormData instanceof SheetFormData) {
            $data = $sheetFormData->getData();
        }

        if ($userFormData instanceof UserFormData) {
            $data = array_merge($data, $userFormData->getData());
        }

        return $this->templateDataFactory->createFormTemplateWithData(
            $query->formTemplate,
            $data,
            $query->locale
        );
    }
}
