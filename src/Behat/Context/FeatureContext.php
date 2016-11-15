<?php

namespace Proximum\Vimeet\Behat\Context;

use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\MinkExtension\Context\MinkContext;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

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
     * @Given the cache is clear
     */
    public function theCacheIsClear()
    {
        exec("bin/console cache:clear --env=test");
        exec("bin/console doctrine:cache:clear-metadata --env=test");
        exec("bin/console doctrine:cache:clear-query --env=test");
        exec("bin/console doctrine:cache:clear-result --env=test");
    }

    /**
     * @Given elastica is populate
     */
    public function eslaticaIsPopulate()
    {
        exec("bin/console fos:elastica:populate --env=test");
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
        $this->checkMailSendToRecipient($type, $email, 'getTo');
    }

    /**
     * @Given /^(?:|the )"(?P<type>[^"]+)" mail should be sent in bcc to "(?P<email>[^"]+)"$/
     */
    public function theMailShouldBeSentInBCCTo($type, $email)
    {
        $this->checkMailSendToRecipient($type, $email, 'getBcc');
    }

    /**
     * @param string $type
     * @param string $email
     * @param string $recipient
     *
     * @throws \Exception
     */
    private function checkMailSendToRecipient($type, $email, $recipient)
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
                $recipients = array_keys($message->$recipient());

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
     * @When /^(?:|I )check radio "([^"]*)"$/
     */
    public function iCheckRadio($radio)
    {
        $page = $this->getSession()->getPage();

        $element = $page->findById($radio);

        if ($element !== null) {
            if ($element->getTagName() === 'input') {
                $this->fillField(
                    $element->getAttribute('name'),
                    true
                );

                return;
            }
        }

        throw new \Exception('Radio button not found');
    }

    /**
     * @When /^The radio "([^"]*)" should be checked$/
     */
    public function theRadioShouldBeChecked($radio)
    {
        $page = $this->getSession()->getPage();

        $element = $page->findById($radio);

        if ($element !== null) {
            if ($element->getTagName() === 'input') {
                // Behat return 1 instead of true for the value of a radio
                if ((bool) $element->getValue() !== true) {
                    throw new \Exception('The radio button is not checked');
                }

                return;
            }
        }

        throw new \Exception('Radio button not found');
    }

    /**
     * @Then /^the radio "([^"]*)" should not be checked$/
     */
    public function theRadioShouldNotBeChecked($radio)
    {
        $page = $this->getSession()->getPage();

        $element = $page->findById($radio);

        if ($element !== null) {
            if ($element->getTagName() === 'input') {
                // Behat return 1 instead of true for the value of a radio
                if ((bool) $element->getValue() === true) {
                    throw new \Exception('The radio button is checked');
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
     * @Given I am logged with :email on event :event
     */
    public function iAmLoggedOnEvent($email, $eventUrl)
    {
        $this->setBaseUrl($eventUrl);
        $driver = $this->getSession()->getDriver();
        if (!$driver instanceof \Behat\Mink\Driver\BrowserKitDriver) {
            throw new \Exception('BrowserKitDriver not supported');
        }

        $client = $driver->getClient();
        $client->getCookieJar()->set(new Cookie(session_name(), true));

        $session = $client->getContainer()->get('session');

        $user = $this->kernel->getContainer()->get('vimeet_infrastructure.repository.user_repository')->findByEmail($email);
        $providerKey = 'main';

        if (null === $user) {
            throw new \Exception('Unknown user');
        }

        $token = new UsernamePasswordToken($user, null, $providerKey, $user->getRoles());
        $session->set('_security_'.$providerKey, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $client->getCookieJar()->set($cookie);
    }

    /**
     * @Given I am logged with :email on admin
     */
    public function iAmLoggedAsAdmin($email)
    {
        $this->setBaseUrl('http://vimeet.proximum.dev');
        $driver = $this->getSession()->getDriver();
        if (!$driver instanceof \Behat\Mink\Driver\BrowserKitDriver) {
            throw new \Exception('BrowserKitDriver not supported');
        }

        $client = $driver->getClient();
        $client->getCookieJar()->set(new Cookie(session_name(), true));

        $session = $client->getContainer()->get('session');

        $user = $this->kernel->getContainer()->get('repository.admin_repository')->findByEmail($email);
        $providerKey = 'admin';

        $token = new UsernamePasswordToken($user, null, $providerKey, $user->getRoles());
        $session->set('_security_'.$providerKey, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $client->getCookieJar()->set($cookie);
    }

    /**
     * Opens specified page.
     *
     * @Given /^(?:|I )am on this page "(?P<page>[^"]+)"$/
     * @When /^(?:|I )go to this page "(?P<page>[^"]+)"$/
     */
    public function goToThisPage($page)
    {
        parent::visit($this->baseUrl . $page);
        $this->assertResponseStatus(200);
    }

    /**
     * Checks, that current page PATH is equal to specified.
     *
     * @Then /^(?:|I )should be on this page "(?P<page>[^"]+)"$/
     */
    public function shouldBeOnThisPage($page)
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
     * Checks, that current url is equal to specified.
     *
     * @Then /^(?:|I )should be on this url "(?P<url>[^"]+)"$/
     */
    public function assertUrl($url)
    {
        $this->assertSession()->addressEquals($url);
        $this->assertResponseStatus(200);
    }

    /**
     * @param $url
     */
    private function setBaseUrl($url)
    {
        $this->baseUrl = $url . '/app_test.php';
    }
}
