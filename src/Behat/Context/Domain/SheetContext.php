<?php

namespace Proximum\Vimeet\Behat\Context\Domain;

use Behat\Behat\Context\Context;
use Proximum\Vimeet\Behat\Context\Domain\Proxy\SheetContextProxyInterface;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;

class SheetContext implements Context
{
    /** @var SheetContextProxyInterface */
    private $sheetContextProxy;

    /**
     * @param SheetContextProxyInterface $sheetContextProxy
     */
    public function __construct(SheetContextProxyInterface $sheetContextProxy)
    {
        $this->sheetContextProxy = $sheetContextProxy;
    }

    /**
     * @Given there is a sheet
     */
    public function thereIsASheet(): void
    {
        $this->thereIsASheetWithTheTitle(null);
    }

    /**
     * @Given there is a sheet of this type
     */
    public function thereIsASheetOfThisType()
    {
        $event = $this->getEvent();

        $type = $this->sheetContextProxy->getStorage()->get('type');

        if (!$type instanceof Type) {
            throw new \LogicException('Missing Type');
        }

        $sheet = $this->sheetContextProxy->getSheetManager()->create($event, null, $type);
        $this->sheetContextProxy->getStorage()->set('sheet', $sheet);
    }

    /**
     * @Given /^this sheet has "(?P<completeness>[^"]+)" as completeness$/
     *
     * @param int $completeness
     */
    public function thisSheetHasCompleteness(int $completeness = 50)
    {
        $sheet = $this->sheetContextProxy->getStorage()->get('sheet');

        if (!$sheet instanceof Sheet) {
            throw new \LogicException('Missing sheet');
        }

        $this->sheetContextProxy->getSheetManager()->updateCompleteness($sheet, $completeness);
        $this->sheetContextProxy->getStorage()->set('sheet', $sheet);
    }

    /**
     * @Given /^there is a sheet with the title "(?P<title>[^"]+)"$/
     *
     * @param string|null $title
     */
    public function thereIsASheetWithTheTitle($title)
    {
        $event = $this->getEvent();
        $sheet = $this->sheetContextProxy->getSheetManager()->create($event, null, null, $title);
        $this->sheetContextProxy->getStorage()->set('sheet', $sheet);
    }

    /**
     * @Given there is a sheet with the title :title registered at :createdAt
     *
     * @param string|null $title
     * @param string|null $createdAt
     */
    public function thereIsASheetWithTheTitleAndCreatedAt($title, $createdAt = 'now')
    {
        $event = $this->getEvent();
        $sheet = $this->sheetContextProxy->getSheetManager()->create($event, null, null, $title, null, $createdAt);
        $this->sheetContextProxy->getStorage()->set('sheet', $sheet);
    }

    /**
     * @Given /^there is a sheet for this type with the title "(?P<title>[^"]+)"$/
     *
     * @param string|null $title
     */
    public function thereIsASheetForThisTypeWithThisTitle($title)
    {
        $event = $this->getEvent();

        $type = $this->sheetContextProxy->getStorage()->get('type');

        if (null === $type) {
            throw new \LogicException('Missing Type');
        }

        $sheet = $this->sheetContextProxy->getSheetManager()->create($event, $this->sheetContextProxy->getStorage()->get('user'), $type, $title);
        $this->sheetContextProxy->getStorage()->set('sheet', $sheet);
    }

    /**
     * @Given /^there is a sheet in this group with the title "(?P<title>[^"]+)"$/
     *
     * @param string|null $title
     */
    public function thereIsASheetInThisGroup($title)
    {
        $event = $this->getEvent();
        $group = $this->sheetContextProxy->getStorage()->get('group');

        if (null === $group) {
            throw new \LogicException('Missing Group');
        }

        $sheet = $this->sheetContextProxy->getSheetManager()->create($event, null, null, $title, $group);

        $this->sheetContextProxy->getStorage()->set('sheet', $sheet);
    }

    /**
     * @Given /^this sheet is in catalog$/
     */
    public function sheetInCatalog()
    {
        $sheet = $this->sheetContextProxy->getStorage()->get('sheet');
        $this->sheetContextProxy->getSheetManager()->setInCatalog($sheet);
    }

    /**
     * @Given /^this sheet is validated$/
     */
    public function sheetValidated()
    {
        $sheet = $this->sheetContextProxy->getStorage()->get('sheet');
        $this->sheetContextProxy->getSheetManager()->setValidated($sheet);
    }

    /**
     * @Given /^this sheet is enabled/
     */
    public function sheetEnabled()
    {
        $sheet = $this->sheetContextProxy->getStorage()->get('sheet');
        $this->sheetContextProxy->getSheetManager()->setEnabled($sheet);
    }

    /**
     * @Given this sheet has :city as city
     */
    public function thisSheetHasCity(string $city)
    {
        /** @var Sheet */
        $sheet = $this->sheetContextProxy->getStorage()->get('sheet');
        $data = $sheet->getRegistrationData();
        $data['d224f0e7']['text'] = $city;
        $sheet->setRegistrationData($data);
    }

    /**
     * @Given this sheet has supply services
     */
    public function thisSheetHasSupplyService()
    {
        /** @var Sheet */
        $sheet = $this->sheetContextProxy->getStorage()->get('sheet');
        $data = $sheet->getData();
        $data['03b394ac']['items'][] = 'ab93de01';
        $data['03b394ac']['items'][] = 'ab93de02';
        $sheet->setData($data);
    }

    /**
     * @Given this sheet has needs
     */
    public function thisSheetHasNeeds()
    {
        /** @var Sheet */
        $sheet = $this->sheetContextProxy->getStorage()->get('sheet');
        $data = $sheet->getData();
        $data['63ccc105']['items'][] = 'ab93de02';
        $data['63ccc105']['items'][] = 'ab93de05';
        $sheet->setData($data);
    }

    /**
     * @Given this sheet supply computing
     */
    public function thisSheetSupplyComputing()
    {
        /** @var Sheet */
        $sheet = $this->sheetContextProxy->getStorage()->get('sheet');
        $data = $sheet->getData();
        $data['03b394ac']['items'][] = 'ab93de05';
        $sheet->setData($data);
    }

    /**
     * @Given this sheet needs prototyping
     */
    public function thisSheetNeedsPrototyping()
    {
        /** @var Sheet */
        $sheet = $this->sheetContextProxy->getStorage()->get('sheet');
        $data = $sheet->getData();
        $data['63ccc105']['items'][] = 'ab93de06';
        $sheet->setData($data);
    }

    /**
     * @throws \LogicException
     *
     * @return Event
     */
    private function getEvent()
    {
        $event = $this->sheetContextProxy->getStorage()->get('event');

        if (!$event instanceof Event) {
            throw new \LogicException('Missing Event');
        }

        return $event;
    }
}
