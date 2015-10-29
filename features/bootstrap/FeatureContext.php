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
        foreach ($this->getSession()->getPage()->findAll('css', 'label') as $label) {
            if ($radioLabel === $label->getText() && $label->has('css', 'input[type="radio"]')) {
                $this->fillField(
                    $label->find('css', 'input[type="radio"]')->getAttribute('name'),
                    $label->find('css', 'input[type="radio"]')->getAttribute('value')
                );
                return;
            }
        }
        throw new \Exception('Radio button not found');
    }
}
