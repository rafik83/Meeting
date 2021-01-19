<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Filter;

use Proximum\Vimeet\Domain\Filter\SheetFilter;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Sheet\SheetFilterType;
use Symfony\Component\Form\FormFactoryInterface;

class SheetFilterSubmittedDataGetter
{
    /** @var SheetFilter */
    private $sheetFilter;

    /** @var FormFactoryInterface */
    private $formFactory;

    public function __construct(SheetFilter $sheetFilter, FormFactoryInterface $formFactory)
    {
        $this->sheetFilter = $sheetFilter;
        $this->formFactory = $formFactory;
    }

    public function handle(Event $event, Admin $user, string $locale)
    {
        $defaultFilters = SheetFilterType::getDefaultFilters();
        $filters = $this->sheetFilter->get($event) ?? $defaultFilters;

        $sheetFilterForm = $this->formFactory->createNamed(
            '',
            SheetFilterType::class,
            $defaultFilters,
            [
                'event' => $event,
                'user' => $user,
                'locale' => $locale,
                'method' => 'GET',
                'csrf_protection' => false,
                'required' => false,
                'allow_extra_fields' => true,
            ]
        );

        $sheetFilterForm->submit($filters);

        return $sheetFilterForm->getData();
    }
}
