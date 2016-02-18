<?php

use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\MinkExtension\Context\MinkContext;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

/**
 * Defines application features from the specific context.
 */
class FeatureContext extends MinkContext implements KernelAwareContext, SnippetAcceptingContext
{
    private $kernel;

    private $baseUrl;

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
     * @return mixed
     */
    protected function getSpoolDir()
    {
        return $this->kernel->getContainer()->getParameter('swiftmailer.spool.default.file.path');
    }

    /**
     * We need to purge the spool between each scenario
     *
     * @BeforeScenario
     */
    public function purgeSpool()
    {
        $spoolDir = $this->getSpoolDir();

        $filesystem = new Filesystem();

        $filesystem->remove($spoolDir);
    }

    /**
     * @param $string
     * @return mixed
     * @throws Exception
     */
    public function getLinkFromA($string)
    {
        preg_match_all('/<a[^>]+href=([\'"])(.+?)\1[^>]*>/i', $string, $result);

        if (!isset($result[2][0])) {
            throw new \Exception(sprintf("The link was not found in \"%s\"", $string));
        }

        return $result[2][0];
    }

    /**
     * @Given /^(?:|the )"(?P<type>[^"]+)" mail should be sent to "(?P<email>[^"]+)"$/
     */
    public function theMailShouldBeSentTo($type, $email)
    {
        $spoolDir = $this->getSpoolDir();

        $filesystem = new Filesystem();

        if ($filesystem->exists($spoolDir)) {
            $finder = new Finder();

            // find every files inside the spool dir except hidden files
            $finder
                ->in($spoolDir)
                ->ignoreDotFiles(true)
                ->files();

            foreach ($finder as $file) {
                $message = unserialize(file_get_contents($file));

                // check the recipients
                $recipients = array_keys($message->getTo());
                if (!in_array($email, $recipients)) {
                    continue;
                }

                // check if this is the correct message type
                $headers = $message->getHeaders();
                if ($headers->has('X-Message-ID')) {
                    $messageId = $headers->get('X-Message-ID')->getValue();

                    if ($messageId == $type) {
                        return;
                    }
                }
            }
        }

        throw new \Exception(sprintf("The \"%s\" was not sent", $type));
    }

    /**
     * @Given /^(?:|the )"(?P<type>[^"]+)" mail should contain the link "(?P<email>[^"]+)"$/
     */
    public function theMailShouldContainTheLink($type, $contain)
    {
        $spoolDir = $this->getSpoolDir();

        $filesystem = new Filesystem();

        if ($filesystem->exists($spoolDir)) {
            $finder = new Finder();

            // find every files inside the spool dir except hidden files
            $finder
                ->in($spoolDir)
                ->ignoreDotFiles(true)
                ->files();

            foreach ($finder as $file) {
                $message = unserialize(file_get_contents($file));

                $headers = $message->getHeaders();
                if ($headers->has('X-Message-ID')) {
                    $messageId = $headers->get('X-Message-ID')->getValue();

                    if ($messageId == $type) {
                        $result = $this->getLinkFromA($message->getBody());

                        if (substr($result, 0, strlen($contain)) === $contain) {
                            return;
                        }
                    }
                }

            }
        }

        throw new \Exception(sprintf("The \"%s\" mail does not contain it", $type));
    }

    /**
     * @Given I follow the :link link in the :type mail
     */
    public function iFollowTheLinkInTheMail($link, $type)
    {
        $spoolDir = $this->getSpoolDir();

        $filesystem = new Filesystem();

        if ($filesystem->exists($spoolDir)) {
            $finder = new Finder();

            // find every files inside the spool dir except hidden files
            $finder
                ->in($spoolDir)
                ->ignoreDotFiles(true)
                ->files();

            foreach ($finder as $file) {
                $message = unserialize(file_get_contents($file));

                $headers = $message->getHeaders();
                if ($headers->has('X-Message-ID')) {
                    $messageId = $headers->get('X-Message-ID')->getValue();

                    if ($messageId == $type) {
                        $result = $this->getLinkFromA($message->getBody());

                        if (substr($result, 0, strlen($link)) === $link) {
                            $this->visitPath($result);
                            return;
                        }
                    }
                }

            }
        }

        throw new \Exception(sprintf("The \"%s\" mail does not contain the link", $type));
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

                if (null === $input) {
                    $for = $label->getAttribute('for');

                    if (null !== $for) {
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
        $tables = $this->getSession()->getPage()->findAll('css', 'table');

        foreach ($tables as $keytb => $table) {
            $numColumnQuantity = null;
            $numColumnCheckbox = null;
            $numLine           = null;

            $thead = $table->findAll('css', 'thead th');
            $tbody = $table->findAll('css', 'tbody tr');

            foreach ($thead as $key => $th) {
                if (strpos($th->getText(), 'quantity')) {
                    $numColumnQuantity = $key + 1;
                }
                if (strpos($th->getText(), 'label')) {
                    $numColumnCheckbox = $key + 1;
                }
            }

            foreach ($tbody as $key => $tr) {
                if (null !== $numColumnCheckbox) {
                    if (null !== $tr->find(
                        'css',
                        sprintf(
                            'td:nth-child(%s):contains("%s")',
                            $numColumnCheckbox,
                            $checkbox
                        )
                    )) {
                        $numLine = $key + 1;
                        break;
                    }
                }
            }

            if (null !== $numColumnQuantity && null !== $numColumnCheckbox && null !== $numLine) {
                $table->find(
                    'css',
                    sprintf('tbody tr:nth-child(%s) td:nth-child(%s) select', $numLine, $numColumnQuantity)
                )->selectOption($quantity);

                return;
            }

        }

        throw new \Exception('Element not found');
    }

    /**
     * @When I should see :something in the column :column for the row containing :row
     */
    public function iShouldSeeInTheRowAndColumn($something, $column, $row)
    {
        $tables = $this->getSession()->getPage()->findAll('css', 'table');

        foreach ($tables as $table) {
            $numColumn = null;

            $ths = $table->findAll('css', 'thead th');

            $cols = 0;
            foreach ($ths as $th) {
                // calculate col num depending on colspan
                $colspan = $th->getAttribute('colspan');
                $cols += $colspan !== null ? $colspan : 1;
                if (strpos($th->getText(), $column) !== false) {
                    $numColumn = $cols;
                }
            }

            if (null !== $numColumn) {
                $trs = $table->findAll('css', 'tbody tr');
                foreach ($trs as $tr) {
                    if (strpos($tr->getText(), $row) !== false) {
                        $tds = $tr->findAll('css', 'td');
                        $cols = 0;
                        foreach ($tds as $td) {
                            // calculate col num depending on colspan
                            $colspan = $td->getAttribute('colspan');
                            $cols += $colspan !== null ? $colspan : 1;

                            if ($cols == $numColumn && false !== strpos($td->getText(), $something)) {
                                return;
                            }
                        }
                    }
                }
            }
        }

        throw new \Exception('Element not found');
    }

    /**
     * @When I wait until I see :something
     */
    public function iWaitUntilISee($something)
    {
        if (!$this->getSession()->wait(1000, sprintf('$("#%s").length', $something))) {
            throw new \Exception(sprintf('%s not found', $something));
        }
    }

    /**
     * @Then I go to :url and I wait until the page is ready
     */
    public function iGoToAndWaitUntilPageIsReady($url)
    {
        $this->visit($url);

        $this->getSession()->maximizeWindow();
        $this->getSession()->wait(5000, 'document.readyState === "complete"');
    }

    /**
     * @Given I am on Admin
     */
    public function iAmOnAdmin()
    {
        $this->setBaseUrl('http://vimeet.proximum.dev');
    }

    /**
     * @Given I am logged with :email and :password on event :event
     */
    public function iAmLoggedOnEvent($email, $password, $eventUrl)
    {
        $this->setBaseUrl($eventUrl);
        $this->visit('/fr/login');
        $this->fillField('form.login.children.username.label', $email);
        $this->fillField('form.login.children.password.label', $password);
        $this->pressButton('form.login.children.submit.label');
        $this->assertResponseStatus(200);
    }

    /**
     * Opens specified page.
     *
     * @Given /^(?:|I )am on this page "(?P<page>[^"]+)"$/
     * @When /^(?:|I )go to this page "(?P<page>[^"]+)"$/
     */
    public function visit($page)
    {
        parent::visit($this->baseUrl . $page);
        $this->assertResponseStatus(200);
    }

    /**
     * Checks, that current page PATH is equal to specified.
     *
     * @Then /^(?:|I )should be on this page "(?P<page>[^"]+)"$/
     */
    public function assertPageAddress($page)
    {
        parent::assertPageAddress($this->baseUrl . $page);
        $this->assertResponseStatus(200);
    }

    /**
     * This step help to debug tests
     *
     * @When I dump the page
     */
    public function iDumpThePage()
    {
        echo $this->getSession()->getPage()->getOuterHtml();
    }

    /**
     * @param $url
     */
    private function setBaseUrl($url)
    {
        $this->baseUrl = $url . '/app_test.php';
    }
}
