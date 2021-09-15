<?php

namespace Proximum\Vimeet\Domain\Model;

class PackageTranslation
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var Package
     */
    private $package;

    /**
     * @var string
     */
    private $locale;

    /**
     * @var string
     */
    private $plansLabel;

    /**
     * @var string
     */
    private $participantAndPlanningLabel;

    /**
     * @var string
     */
    private $optionsLabel;

    /**
     * PackageTranslation constructor.
     *
     * @param Package $package
     * @param string  $locale
     * @param string  $plansLabel
     * @param string  $participantAndPlanningLabel
     * @param string  $optionsLabel
     */
    public function __construct(Package $package, $locale, $plansLabel, $participantAndPlanningLabel, $optionsLabel)
    {
        $this->package            = $package;
        $this->locale                      = $locale;
        $this->plansLabel               = $plansLabel;
        $this->participantAndPlanningLabel = $participantAndPlanningLabel;
        $this->optionsLabel                = $optionsLabel;
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $plansLabel
     * @param string $participantAndPlanningLabel
     * @param string $optionsLabel
     */
    public function set($plansLabel, $participantAndPlanningLabel, $optionsLabel)
    {
        $this->plansLabel                  = $plansLabel;
        $this->participantAndPlanningLabel = $participantAndPlanningLabel;
        $this->optionsLabel                = $optionsLabel;
    }

    /**
     * Get package
     *
     * @return Package
     */
    public function getPackage()
    {
        return $this->package;
    }

    /**
     * Get locale
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Get plansLabel
     *
     * @return string
     */
    public function getPlansLabel()
    {
        return $this->plansLabel;
    }

    /**
     * Get participantAndPlanningLabel
     *
     * @return string
     */
    public function getParticipantAndPlanningLabel()
    {
        return $this->participantAndPlanningLabel;
    }

    /**
     * Get optionsLabel
     *
     * @return string
     */
    public function getOptionsLabel()
    {
        return $this->optionsLabel;
    }
}
