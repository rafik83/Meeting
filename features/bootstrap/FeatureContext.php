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
}
