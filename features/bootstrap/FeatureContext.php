<?php

use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\MinkExtension\Context\MinkContext;
use Behat\Symfony2Extension\Context\KernelAwareContext;

/**
 * Defines application features from the specific context.
 */
class FeatureContext extends MinkContext implements KernelAwareContext, SnippetAcceptingContext
{
    private $kernel;

    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     */
    public function __construct()
    {
    }

    /**
     * @param \Symfony\Component\HttpKernel\KernelInterface $kernel
     */
    public function setKernel(\Symfony\Component\HttpKernel\KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * @When /^(?:|I )check the "([^"]*)" radio$/
     */
    public function iCheckTheRadio($radioLabel)
    {
        $page = $this->getSession()->getPage();

        foreach ($page->findAll('css', 'label') as $label) {
            if ($radioLabel === $label->getText()) {
                $input = $label->find('css', 'input[type="radio"]');

                if (null == $input) {
                    $for = $label->getAttribute('for');

                    if (null != $for) {
                        $input = $page->find('named', ['id', $for]);
                    }
                }

                if ($input) {
                    $this->fillField(
                        $input->getAttribute('name'),
                        $input->getAttribute('value')
                    );

                    return;
                }

                return;
            }
        }

        throw new \Exception('Radio button not found');
    }

    /**
     * @When I select the quantity :quantity for the checkbox :checkbox
     */
    public function iSelectTheQuantityForTheCheckbox($quantity, $checkbox)
    {
        $page = $this->getSession()->getPage();

        $thead = $this->getSession()->getPage()->findAll('css', 'table thead th');
        $tbody = $this->getSession()->getPage()->findAll('css', 'table tbody tr');

        $numColumnQuantity = null;
        $numColumnCheckbox = null;
        $numLine           = null;

        foreach ($thead as $key => $th) {
            if (strpos($th->getText(), 'quantity')) {
                $numColumnQuantity = $key + 1;
            }
            if (strpos($th->getText(), 'label')) {
                $numColumnCheckbox = $key + 1;
            }
        }

        if (null != $numColumnCheckbox) {
            foreach ($tbody as $key => $td)
            if (null != $this->getSession()->getPage()->find(
                'css',
                sprintf('table tbody tr:nth-child(%s) td:nth-child(%s):contains("%s")', $key+1, $numColumnCheckbox, $checkbox)
            )) {
                $numLine = $key + 1;
                break;
            }
        }

        if (null !== $numColumnQuantity && null != $numColumnCheckbox && null != $numLine) {
            $this->getSession()->getPage()->find(
                'css',
                sprintf('table tbody tr:nth-child(%s) td:nth-child(%s) select', $numLine, $numColumnQuantity)
            )->selectOption($quantity);

            return;
        }

        throw new \Exception('Element not found');
    }
}
