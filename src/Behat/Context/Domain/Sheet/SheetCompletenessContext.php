<?php

namespace Proximum\Vimeet\Behat\Context\Domain\Sheet;

use Behat\Behat\Context\Context;
use Proximum\Vimeet\Behat\Context\Domain\Proxy\AdminContextProxyInterface;
use Proximum\Vimeet\Behat\Context\Domain\Proxy\Sheet\SheetCompletenessContextProxyInterface;

class SheetCompletenessContext implements Context
{
    /** @var SheetCompletenessContextProxyInterface */
    private $sheetCompletenessContextProxy;

    /**
     * @param SheetCompletenessContextProxyInterface $sheetCompletenessContextProxy
     */
    public function __construct(SheetCompletenessContextProxyInterface $sheetCompletenessContextProxy)
    {
        $this->sheetCompletenessContextProxy = $sheetCompletenessContextProxy;
    }

    /**
     * @Given /^the completeness of this sheet for the locale "(?P<locale>[^"]+)" is (?P<completeness>\d+)$/
     *
     * @param string $locale
     * @param int $completeness
     */
    public function createSheetCompletenessLocalized(string $locale, int $completeness): void
    {
        $sheet = $this->sheetCompletenessContextProxy->getStorage()->get('sheet');

        if (null === $sheet) {
            throw new \InvalidArgumentException('Missing Sheet');
        }

        $sheetCompleteness = $this->sheetCompletenessContextProxy
            ->getSheetCompletenessManager()
            ->setCompleteness($sheet, $locale, $completeness);

        $this->sheetCompletenessContextProxy->getStorage()->set('sheet_completeness', $sheetCompleteness);
    }
}
