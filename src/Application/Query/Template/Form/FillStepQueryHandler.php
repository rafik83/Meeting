<?php

namespace Proximum\Vimeet\Application\Query\Template\Form;

use Proximum\Vimeet\Application\Adapter\MarkdownAdapterInterface;
use Proximum\Vimeet\Application\View\Template\Form\BlockStepView;
use Proximum\Vimeet\Domain\Template\Block;
use Proximum\Vimeet\Domain\Template\Exception\BlockForGivenStepNotFoundException;
use Proximum\Vimeet\Domain\Template\Exception\GivenStepIsRequiredAndNotFilledException;

class FillStepQueryHandler
{
    /** @var FormTemplateDataQueryHandler */
    private $formTemplateDataQueryHandler;

    /** @var MarkdownAdapterInterface */
    private $markdownAdapter;

    /** @var BreadCrumbViewQueryHandler */
    private $breadCrumbViewQueryHandler;

    public function __construct(
        FormTemplateDataQueryHandler $formTemplateDataQueryHandler,
        MarkdownAdapterInterface $markdownAdapter,
        BreadCrumbViewQueryHandler $breadCrumbViewQueryHandler
    ) {
        $this->formTemplateDataQueryHandler = $formTemplateDataQueryHandler;
        $this->markdownAdapter = $markdownAdapter;
        $this->breadCrumbViewQueryHandler = $breadCrumbViewQueryHandler;
    }

    /**
     * @param FillStepQuery $query
     *
     * @return BlockStepView
     *
     * @throws BlockForGivenStepNotFoundException
     */
    public function handle(FillStepQuery $query): BlockStepView
    {
        $templateData = $this->formTemplateDataQueryHandler->handle(
            new FormTemplateDataQuery(
                $query->formTemplate,
                $query->sheet,
                $query->participant,
                $query->locale
            )
        );

        $block = $templateData->getBlock($query->step);

        if (!$block instanceof Block) {
            throw new BlockForGivenStepNotFoundException($query->step);
        }

        $breadCrumb = $this->breadCrumbViewQueryHandler->handle(new BreadCrumbViewQuery($templateData, $query->step, $query->locale));

        $currentStep = $breadCrumb->getCurrentStep();

        if (false === $currentStep->accessible) {
            $previousStepIndex = 1;
            foreach ($breadCrumb->steps as $stepIndex => $step) {
                if (!$step->accessible) {
                    throw new GivenStepIsRequiredAndNotFilledException($previousStepIndex);
                }

                $previousStepIndex = $stepIndex;
            }
        }

        return new BlockStepView(
            $block,
            $this->markdownAdapter->toHtml($block->getDescription($query->locale)),
            $breadCrumb
        );
    }
}
