<?php

namespace Proximum\Vimeet\Application\Components\Meeting;

use Proximum\Vimeet\Domain\Event\Day\DDayGuesser;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Repository\RuleRepositoryInterface;

class AllowTransformRequestIntoMeeting
{
    /**
     * @var RuleRepositoryInterface
     */
    private $ruleRepository;

    public function __construct(RuleRepositoryInterface $ruleRepository)
    {
        $this->ruleRepository = $ruleRepository;
    }

    public function __invoke(Request $request)
    {
        if (!$request->getEvent()->getConfiguration()->isVisio()) {
            return false;
        }

        $rules = $this->ruleRepository->getBySeerSheetAndSeeableSheet($request->getFromSheet(), $request->getToSheet());

        foreach ($rules as $rule) {
            if ($rule->getRequestAutomaticallyTransformedIntoMeeting()) {
                return true;
            }
        }

        return false;
    }
}
