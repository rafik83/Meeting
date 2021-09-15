<?php

namespace Proximum\Vimeet\Behat\Context\Domain;

use Behat\Behat\Context\Context;
use Proximum\Vimeet\Behat\Context\Domain\Proxy\TipContextProxyInterface;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Tip\Tip;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Tests\Factory\TipFactory;

class TipContext implements Context
{
    /** @var TipContextProxyInterface */
    private $tipContextProxy;

    /**
     * @param TipContextProxyInterface $tipContextProxy
     */
    public function __construct(TipContextProxyInterface $tipContextProxy)
    {
        $this->tipContextProxy = $tipContextProxy;
    }

    /**
     * @Given /^the tip "(?P<tipTitle>[^"]+)" is created$/
     *
     * @param string $title
     */
    public function createTip($title)
    {
        $tip = $this->tipContextProxy->getTipManager()->create($title);
        $this->tipContextProxy->getStorage()->set('tip', $tip);
    }

    /**
     * @Given /^the tip "(?P<tipTitle>[^"]+)" is created for the event "(?P<eventTitle>[^"]+)"$/
     *
     * @param string $title
     * @param string $eventTitle
     */
    public function createTipForEvent($title, $eventTitle)
    {
        $tip = $this->tipContextProxy->getTipManager()->createForEvent($title, $eventTitle);
        $this->tipContextProxy->getStorage()->set('tip', $tip);
    }

    /**
     * @Given /^the tip "(?P<tipTitle>[^"]+)" is created for the type of this sheet$/
     *
     * @param string $title
     */
    public function createTipForThisType(string $title)
    {
        $sheet = $this->tipContextProxy->getStorage()->get('sheet');

        if (!$sheet instanceof Sheet) {
            throw new \InvalidArgumentException('The sheet is not present in the storage');
        }

        $type = $sheet->getType();

        $tip = $this->tipContextProxy->getTipManager()->createForGivenType($title, $type);

        $this->tipContextProxy->getStorage()->set('tip', $tip);
    }

    /**
     * @throws \InvalidArgumentException
     *
     * @return Tip
     */
    private function getTipFromStorage(): Tip
    {
        $tip = $this->tipContextProxy->getStorage()->get('tip');

        if (!$tip instanceof Tip) {
            throw new \InvalidArgumentException('The tip is not present in the storage');
        }

        return $tip;
    }

    /**
     * @Given /^the "(?P<locale>[^"]+)" title translation of this tip is "(?P<title>[^"]+)"$/
     *
     * @param string $locale
     * @param string $title
     */
    public function translateTitle(string $locale, string $title)
    {
        $tip = $this->getTipFromStorage();

        $this->tipContextProxy->getTipManager()->translateTitle($tip, $locale, $title);
    }

    /**
     * @Given this tip is affected on the catalog
     */
    public function affectOnCatalog()
    {
        $tip = $this->getTipFromStorage();

        $this->tipContextProxy->getTipManager()->affectOnCatalog($tip);
    }

    /**
     * @Given this tip is affected on the meeting request management
     */
    public function affectOnMeetingManagement()
    {
        $tip = $this->getTipFromStorage();

        $this->tipContextProxy->getTipManager()->affectOnMeetingManagement($tip);
    }

    /**
     * @Given /^a tip "(?P<tipTitle>[^"]+)" is enabled on confirmation phone context for this type$/
     *
     * @param string $tipTitle
     */
    public function aTipIsEnabledOnConfirmationPhoneContextForThisType($tipTitle)
    {
        $type = $this->tipContextProxy->getStorage()->get('type');

        if (!$type instanceof Type) {
            throw new \LogicException('Missing Type');
        }

        $this->aTipIsEnabledOn($tipTitle, $type, [TipFactory::ON_CONFIRMATION_PHONE => true]);
    }

    /**
     * @param string $tipTitle
     * @param Type   $type
     * @param array  $contexts
     */
    private function aTipIsEnabledOn($tipTitle, Type $type, array $contexts)
    {
        $tip = $this->tipContextProxy->getTipManager()->affectToType($tipTitle, $type, $contexts);
        $this->tipContextProxy->getStorage()->set('tip', $tip);
    }
}
