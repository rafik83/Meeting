<?php

namespace Proximum\Vimeet\Application\ThirdParty\Openl10n;

use Proximum\Vimeet\Application\Adapter\MailerInterface;
use Proximum\Vimeet\Openl10n\Cli\File\FileHandler;
use Proximum\Vimeet\Openl10n\Cli\Project\ProjectHandler;
use Proximum\Vimeet\Openl10n\Cli\ServiceContainer\Configuration\ConfigurationLoader;
use Proximum\Vimeet\Openl10n\Sdk\Api;
use Proximum\Vimeet\Openl10n\Sdk\Config;
use Proximum\Vimeet\Openl10n\Sdk\EntryPoint\ProjectEntryPoint;
use Proximum\Vimeet\Openl10n\Sdk\EntryPoint\ResourceEntryPoint;
use Proximum\Vimeet\Openl10n\Sdk\Model\Language;
use Proximum\Vimeet\Openl10n\Sdk\Model\Resource;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Translation\TranslationsDownloadedMail;

class PullHandler
{
    /** @var string */
    private $configFilePath;

    /** @var MailerInterface */
    private $mailer;

    /** @var string */
    private $mailSender;

    public function __construct(string $configFilePath, MailerInterface $mailer, string $mailSender)
    {
        $this->configFilePath = $configFilePath;
        $this->mailer = $mailer;
        $this->mailSender = $mailSender;
    }

    public function handle(Pull $command): PullResult
    {
        $configurationLoader = new ConfigurationLoader(
            \dirname($this->configFilePath), basename($this->configFilePath)
        );
        $configArray = $configurationLoader->loadConfiguration();

        $config = new Config($configArray['server']['hostname'], $configArray['server']['use_ssl']);
        $config->setAuth($configArray['server']['username'], $configArray['server']['password']);
        $api = new Api($config);

        /** @var ProjectEntryPoint $projectApi */
        $projectApi = $api->getEntryPoint('project');
        /** @var ResourceEntryPoint $resourceApi */
        $resourceApi = $api->getEntryPoint('resource');

        //
        // Get project
        //
        $projectSlug = (new ProjectHandler('vimeet'))->getProjectSlug();
        $project = $projectApi->get($projectSlug);

        //
        // Get project locales
        //
        $languages = $projectApi->getLanguages($project->getSlug());
        $localesToPull = array_map(
            static function (Language $language) {
                return $language->getLocale();
            },
            $languages
        );

        $defaultLocale = $project->getDefaultLocale();

        // Deduplicate values
        $localesToPull = array_unique($localesToPull);

        //
        // Retrieve existing project's resources
        //
        $resources = $resourceApi->findByProject($project);
        // Set resources' pathname as array key
        $resources = array_combine(
            array_map(
                static function (Resource $resource) {
                    return $resource->getPathname();
                },
                $resources
            ),
            $resources
        );

        //
        // Iterate over resources
        //
        $fileHandler = new FileHandler($configurationLoader, $configurationLoader->loadConfiguration()['files']);
        $fileSets = $fileHandler->getFileSets();

        $pullResult = new PullResult();

        foreach ($fileSets as $fileSet) {
            $files = $fileSet->getFiles();
            $options = $fileSet->getOptions('pull');

            foreach ($files as $file) {
                $resourceIdentifier = $file->getPathname(['locale' => $defaultLocale]);
                $locale = $file->getAttribute('locale');

                if (!isset($resources[$resourceIdentifier])) {
                    $pullResult->addSkippedFiles($file->getRelativePathname());
                    continue;
                }

                $resource = $resources[$resourceIdentifier];

                // Ignore non specified locales
                if (!\in_array($locale, $localesToPull, true)) {
                    continue;
                }

                $pullResult->addDownloadedFiles($file->getRelativePathname());
                $content = $resourceApi->export($resource, $locale, $options);

                file_put_contents($file->getAbsolutePathname(), $content);
            }
        }

        if (null !== $command->emailToNotify) {
            $this->mailer->send(
                new TranslationsDownloadedMail(
                    $this->mailSender,
                    $command->emailToNotify,
                    $command->locale
                )
            );
        }

        return $pullResult;
    }
}
