<?php

namespace Proximum\Vimeet\Application\Query\Template\Form;

use Proximum\Vimeet\Application\View\Template\Form\BreadCrumbView;
use Proximum\Vimeet\Application\View\Template\Form\StepView;

class BreadCrumbViewQueryHandler
{
    public function handle(BreadCrumbViewQuery $query): BreadCrumbView
    {
        $templateData = $query->templateData;
        $steps = [];

        $accessible = true;
        foreach ($templateData->getBlocksAsSteps() as $formStep => $block) {
            $hasRemainingFields = false;

            if ($accessible !== false) {
                foreach ($block->getEditableObjects() as $object) {
                    if (true === $object->getRequired() && true === $object->isEmpty()) {
                        $hasRemainingFields = true;
                        break;
                    }
                }
            }

            $steps[$formStep] = new StepView($formStep, $block->getTitle($query->locale), $accessible);

            if ($hasRemainingFields === true) {
                $accessible = false;
            }
        }

        return new BreadCrumbView($steps, $query->currentStep);
    }
}
