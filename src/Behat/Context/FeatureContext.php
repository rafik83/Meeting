<?php

namespace Proximum\Vimeet\Behat\Context;

use Behat\MinkExtension\Context\MinkContext;
use Behat\Mink\Driver\BrowserKitDriver;
use Behat\Mink\Element\NodeElement;
use Proximum\Vimeet\Application\Adapter\FileSystemAdapterInterface;
use Proximum\Vimeet\Behat\Context\Domain\Proxy\FeatureContextProxyInterface;
use Proximum\Vimeet\Behat\Service\Adapter\SMS\StorageProvider;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\AdminRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * Defines application features from the specific context.
 */
class FeatureContext extends MinkContext
{
    private ?string $baseUrl = null;
    private FeatureContextProxyInterface $featureContextProxy;
    private KernelInterface $kernel;
    private UserRepositoryInterface $userRepository;
    private AdminRepositoryInterface $adminRepository;
    private FileSystemAdapterInterface $fileSystem;

    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     */
    public function __construct(
        KernelInterface $kernel,
        FeatureContextProxyInterface $featureContextProxy,
        UserRepositoryInterface $userRepository,
        AdminRepositoryInterface $adminRepository,
        FileSystemAdapterInterface $fileSystem
    )
    {
        $this->kernel = $kernel;
        $this->featureContextProxy = $featureContextProxy;
        $this->fileSystem = $fileSystem;
        $this->userRepository = $userRepository;
        $this->adminRepository = $adminRepository;
    }

    /**
     * @return mixed
     */
    protected function getSpoolDir()
    {
        return $this->kernel->getContainer()->getParameter('swiftmailer.spool.default.file.path');
    }

    /**
     * Convert "vimeet" string to Vimeet sender mail
     *
     * @param string $mail
     *
     * @return string
     */
    protected function evaluateMail($mail)
    {
        return 'vimeet' === $mail ? $this->kernel->getContainer()->getParameter('mailer_sender') : $mail;
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

        $client = $this->getClient();
        $client->getCookieJar()->clear();
    }

    /**
     * @Given the cache is clear
     */
    public function theCacheIsClear()
    {
        exec('bin/console cache:clear --env=test');
        exec('bin/console doctrine:cache:clear-metadata --env=test');
        exec('bin/console doctrine:cache:clear-query --env=test');
        exec('bin/console doctrine:cache:clear-result --env=test');
    }

    /**
     * @Given elastica is populate
     */
    public function elasticaIsPopulate()
    {
        exec('bin/console fos:elastica:reset --env=test --no-debug');
        exec('bin/console fos:elastica:populate --env=test --quiet --no-interaction --index=sheet --no-debug');
    }

    /**
     * @param $string
     *
     * @throws \Exception
     *
     * @return mixed
     */
    public function getLinkFromA($string)
    {
        preg_match_all('/<a[^>]+href=([\'"])(.+?)\1[^>]*>/i', $string, $result);

        if (!isset($result[2][0])) {
            throw new \Exception(sprintf('The link was not found in "%s"', $string));
        }

        return $result[2][0];
    }

    /**
     * @Given /^(?:|the )"(?P<type>[^"]+)" mail should be sent to "(?P<email>[^"]+)" from "(?P<sender>[^"]+)"$/
     */
    public function theMailShouldBeSentTo($type, $email, $senderEmail)
    {
        $this->checkMailSendToRecipient($type, $email, 'to', $senderEmail);
    }

    /**
     * @Given /^(?:|the )"(?P<type>[^"]+)" mail should be sent in bcc to "(?P<email>[^"]+)" from "(?P<sender>[^"]+)"$/
     */
    public function theMailShouldBeSentInBCCTo($type, $email, $senderEmail)
    {
        $this->checkMailSendToRecipient($type, $email, 'bcc', $senderEmail);
    }

    /**
     * @param string $type
     * @param string $email
     * @param string $recipient
     * @param string $senderEmail
     *
     * @throws \Exception
     */
    private function checkMailSendToRecipient($type, $email, $recipient, $senderEmail)
    {
        $email       = $this->evaluateMail($email);
        $senderEmail = $this->evaluateMail($senderEmail);

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
                /** @var \Swift_Message $message */
                $message = unserialize(file_get_contents($file));

                $messageRecipients = [];

                if ('to' == $recipient) {
                    $messageRecipients = $message->getTo();
                } elseif ('bcc' == $recipient) {
                    $messageRecipients = $message->getBcc();
                }

                // check the recipients
                $recipients = array_keys($messageRecipients);
                $sender = key($message->getFrom());

                if (!in_array($email, $recipients)) {
                    continue;
                }

                // check if this is the correct message type
                $headers = $message->getHeaders();
                if ($headers->has('X-Message-ID')) {
                    $messageId = $headers->get('X-Message-ID')->getValue();

                    if ($messageId == $type) {
                        if ($sender != $senderEmail) {
                            throw new \Exception(
                                sprintf('The "%s" was not sent from "%s", instead sender was "%s"', $type, $senderEmail, $sender)
                            );
                        }

                        return;
                    }
                }
            }
        }

        throw new \Exception(sprintf('The "%s" was not sent', $type));
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

        throw new \Exception(sprintf('The "%s" mail does not contain it', $type));
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

        throw new \Exception(sprintf('The "%s" mail does not contain the link', $type));
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

        if (null !== $element) {
            if ('input' === $element->getTagName()) {
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
     * @Then /^the radio "([^"]*)" should be checked$/
     */
    public function theRadioShouldBeChecked($radio)
    {
        $page = $this->getSession()->getPage();

        $element = $page->findById($radio);

        if (null !== $element) {
            if ('input' === $element->getTagName()) {
                if (!$element->isChecked()) {
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

        if (null !== $element) {
            if ('input' === $element->getTagName()) {
                // Behat return 1 instead of true for the value of a radio
                if (true === (bool) $element->getValue()) {
                    throw new \Exception('The radio button is checked');
                }

                return;
            }
        }

        throw new \Exception('Radio button not found');
    }

    /**
     * @Then :option should be selected in :select input
     */
    public function optionShouldBeSelectedInInput($option, $select)
    {
        $page = $this->getSession()->getPage();

        $optionElements = $page->findAll('css', sprintf('#%s > option', $select));

        foreach ($optionElements as $optionElement) {
            if ($optionElement->getAttribute('value') === $option || $optionElement->getText() === $option) {
                if ($optionElement->isSelected()) {
                    return;
                }
            }
        }

        throw new \Exception(sprintf('Selected option %s not found in %s select', $option, $select));
    }

    /**
     * @Then the index :index of the table should contain :title
     */
    public function theGivenIndexShouldContain(int $index = 1, string $title)
    {
        $tables = $this->getSession()->getPage()->findAll('css', 'table');

        /** @var NodeElement $table */
        $table = reset($tables);

        $trs = $table->findAll('css', sprintf('tbody > tr:nth-child(%d)', $index));

        $tr = reset($trs);

        if (false === $tr) {
            throw new \Exception(sprintf('The element of index %d does not exist', $index));
        }

        if (false === strpos($tr->getHtml(), $title)) {
            throw new \Exception(sprintf('The element of index %d does not contain %s', $index, $title));
        }
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
                $cols += null !== $colspan ? $colspan : 1;
                if (false !== strpos($th->getText(), $column)) {
                    $numColumn = $cols;
                }
            }

            if (null !== $numColumn) {
                $trs = $table->findAll('css', 'tbody tr');
                foreach ($trs as $tr) {
                    if (false !== strpos($tr->getText(), $row)) {
                        $tds = $tr->findAll('css', 'td');
                        $cols = 0;
                        foreach ($tds as $td) {
                            // calculate col num depending on colspan
                            $colspan = $td->getAttribute('colspan');
                            $cols += null !== $colspan ? $colspan : 1;

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

        $client = $this->getClient();
        $client->getCookieJar()->set(new Cookie(session_name(), true));

        $session = $client->getContainer()->get('session');

        $user = $this->userRepository->findByEmail($email);
        $providerKey = 'main';

        if (null === $user) {
            throw new \Exception('Unknown user');
        }

        $token = new UsernamePasswordToken($user, null, $providerKey, $user->getRoles());
        $session->set('_security_' . $providerKey, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $client->getCookieJar()->set($cookie);
    }

    /**
     * @Given I am logged with :email on admin
     */
    public function iAmLoggedAsAdminWithGivenEmail($email)
    {
        $this->setBaseUrl('http://admin.vimeet.proximum');

        $client = $this->getClient();
        $client->getCookieJar()->set(new Cookie(session_name(), true));

        $session = $client->getContainer()->get('session');

        $user = $this->adminRepository->findByEmail($email);
        $providerKey = 'admin';

        $token = new UsernamePasswordToken($user, null, $providerKey, $user->getRoles());
        $session->set('_security_' . $providerKey, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $client->getCookieJar()->set($cookie);
    }

    /**
     * @Given I am logged with :email on front
     */
    public function iAmLoggedAsUserWithGivenEmailOnFront($email)
    {
        /** @var Event */
        $event = $this->featureContextProxy->getStorage()->get('event');
        if (null === $event) {
            throw new \InvalidArgumentException('Missing Event');
        }

        $this->setBaseUrl('http://'.$event->getDomain());

        $client = $this->getClient();
        $client->getCookieJar()->set(new Cookie(session_name(), true));

        $session = $client->getContainer()->get('session');

        $user = $this->userRepository->findByEmail($email);
        $providerKey = 'main';

        $token = new UsernamePasswordToken($user, null, $providerKey, $user->getRoles());
        $session->set('_security_' . $providerKey, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $client->getCookieJar()->set($cookie);
    }

    /**
     * @Given I am logged as admin
     */
    public function iAmLoggedAsAdmin()
    {
        $adminRepository = $this->adminRepository;
        $admin = $adminRepository->findOneByRole('ROLE_SUPER_ADMIN');

        if (null !== $admin) {
            return $this->iAmLoggedAsAdminWithGivenEmail($admin->getEmail());
        }

        $email = sprintf('%s@example.net', uniqid());
        $admin = new Admin(
            $email,
            'D/TBAVl5oYyYU6/4F7gOT0mQkbBD8c5rBHga80zO',
            'YzzBNEhw7I6H5xPuziQEAPAsg5g=',
            'fr',
            'Firstname',
            'Lastname',
            'ROLE_SUPER_ADMIN',
            new \DateTime()
        ); // password: vimeet_admin
        $adminRepository->add($admin);

        return $this->iAmLoggedAsAdminWithGivenEmail($email);
    }

    /**
     * Opens specified page.
     *
     * @Given /^(?:|I )am on this page "(?P<page>[^"]+)"$/
     * @When /^(?:|I )go to this page "(?P<page>[^"]+)"$/
     */
    public function goToThisPage($page)
    {
        $this->visit($this->baseUrl . $page);
        $this->assertResponseStatus(200);
    }

    /**
     * Page returns 404
     *
     * @Then /^this page "(?P<page>[^"]+)" returns 404$/
     */
    public function pageReturns404($page)
    {
        $this->visit($this->baseUrl . $page);
        $this->assertResponseStatus(404);
    }

    /**
     * Page returns 403
     *
     * @Then /^this page "(?P<page>[^"]+)" returns 403$/
     */
    public function pageReturns403($page)
    {
        $this->visit($this->baseUrl . $page);
        $this->assertResponseStatus(403);
    }

    /**
     * Event page returns 404 during template rendering
     *
     * @Then /^this event page "(?P<page>[^"]+)" returns 404$/
     */
    public function eventPageReturns404(string $eventPageUrl)
    {
        try {
            $this->visit($eventPageUrl);
        } catch (\Exception $exception) {
            $this->assertResponseStatus(404);
        }
    }

    /**
     * Checks, that current page PATH is equal to specified.
     *
     * @Then /^(?:|I )should be on this page "(?P<page>[^"]+)"$/
     */
    public function shouldBeOnThisPage($page)
    {
        $this->assertPageAddress($this->baseUrl . $page);
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
        $this->baseUrl = $url;
    }

    /**
     * This step help to debug tests
     *
     * @Given I am on the homepage of this event
     */
    public function iAmOnHomePageOfThisEvent()
    {
        /** @var Event|null $event */
        $event = $this->featureContextProxy->getStorage()->get('event');

        if (null === $event) {
            throw new \InvalidArgumentException('Missing Event');
        }

        $this->setBaseUrl(sprintf('http://%s', $event->getDomain()));
        $this->goToThisPage('/');
    }

    /**
     * @Then a SMS should be sent to :phone with content :content
     *
     * @param string $phone
     * @param string $expectedContent
     */
    public function aSmsShouldBeSentTo(string $phone, string $expectedContent)
    {
        $smsDirectory = $this->kernel->getContainer()->getParameter('sms_directory');
        $file = $smsDirectory . DIRECTORY_SEPARATOR . StorageProvider::getFileName($phone);

        if (!$this->fileSystem->exists($file)) {
            throw new \LogicException('Missing SMS');
        }

        if (file_get_contents($file) !== $expectedContent) {
            throw new \LogicException('SMS content is not the expected one');
        }
    }

    /**
     * @Given I am on the homepage of the admin
     */
    public function iAmOnHomePageOfTheAdmin()
    {
        $this->setBaseUrl('http://admin.vimeet.proximum');
        $this->goToThisPage('/');
    }

    /**
     * @Then /^(?:|I )should see "(?P<text>(?:[^"]|\\")*)" in the title of the "(?P<element>[^"]*)" element$/
     */
    public function assertElementTitleContainsText($element, $text)
    {
        $element = $this->assertSession()->elementExists('css', $element);

        if (null === $element) {
            throw new \InvalidArgumentException('Element not found');
        }

        if ($this->fixStepArgument($text) !== $element->getAttribute('title')) {
            throw new \InvalidArgumentException('Element does not contain this text');
        }
    }

    /**
     * @Given I fill my billing informations
     */
    public function iFillMyBillingInformations()
    {
        $this->fillField('gender.man', 'man');
        $this->fillField('form.billing_info_update.children.lastname.label', 'Jean');
        $this->fillField('form.billing_info_update.children.firstname.label', 'DUPOND');
        $this->fillField('form.billing_info_update.children.function.label', 'Director');
        $this->fillField('form.billing_info_update.children.phone.label', '+33456789');
        $this->fillField('form.billing_info_update.children.mobile.label', '+33456789');
        $this->fillField('form.billing_info_update.children.email.label', 'jean.dupond@example.net');
        $this->fillField('form.billing_info_update.children.company.label', 'DUPOND and Brothers');
        $this->fillField('form.billing_info_update.children.street.label', '10 Bucarest street');
        $this->fillField('form.billing_info_update.children.zipcode.label', '75002');
        $this->fillField('form.billing_info_update.children.city.label', 'Paris');
        $this->fillField('form.billing_info_update.children.country.label', 'FR');
        $this->fillField('form.billing_info_update.children.vatNumber.label', '123456789');
    }

    /**
     * @Then /^(?:|I )should not see field "(?P<field>[^"]+)"$/
     */
    public function iShouldNotSeeField($field)
    {
        $found = $this->getSession()->getPage()->hasField($field);

        if (true === $found) {
            throw new \InvalidArgumentException(sprintf('Element %s found', $field));
        }
    }

    /**
     * Clicks link with specified id|title|alt|text in specified element
     * Example: When I follow "Log In" in the "div#button" element
     *
     * @When /^(?:|I )follow "(?P<link>(?:[^"]|\\")*)" in the "(?P<element>[^"]*)" element$/
     */
    public function clickInElementLink($element, $link)
    {
        $element = $this->assertSession()->elementExists('css', $element);

        $link = $this->fixStepArgument($link);
        $element->clickLink($link);
    }

    /**
     * @Then I follow delete for type :type
     */
    public function iFollowDeleteForType($type)
    {
        $locator = sprintf("//td[text()[contains(., '%s')]]/..//form/button[@data-confirm='admin.type.action.confirmDelete']", $type);
        $button = $this->getSession()->getPage()->find('xpath', $locator);
        $button->press();
    }

    /**
     * @Then I follow edit for type :type
     */
    public function iFollowEditForType($type)
    {
        $locator = sprintf("//td[text()[contains(., '%s')]]/..//a[text()[contains(., 'admin.type.update.link')]]", $type);
        $editLink = $this->getSession()->getPage()->find('xpath', $locator);
        $this->getSession()->visit($editLink->getAttribute('href'));
    }

    private function getClient(): KernelBrowser
    {
        $driver = $this->getSession()->getDriver();
        if (!$driver instanceof BrowserKitDriver) {
            throw new \Exception('BrowserKitDriver not supported');
        }

        return $driver->getClient();
    }
}
