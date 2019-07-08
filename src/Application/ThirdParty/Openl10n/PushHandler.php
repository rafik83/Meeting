<?php

namespace Proximum\Vimeet\Application\ThirdParty\Openl10n;

use GuzzleHttp\Exception\BadResponseException;
use Proximum\Vimeet\Openl10n\Cli\File\FileHandler;
use Proximum\Vimeet\Openl10n\Cli\Project\ProjectHandler;
use Proximum\Vimeet\Openl10n\Cli\ServiceContainer\Configuration\ConfigurationLoader;
use Proximum\Vimeet\Openl10n\Sdk\Api;
use Proximum\Vimeet\Openl10n\Sdk\Config;
use Proximum\Vimeet\Openl10n\Sdk\EntryPoint\ProjectEntryPoint;
use Proximum\Vimeet\Openl10n\Sdk\EntryPoint\ResourceEntryPoint;
use Proximum\Vimeet\Openl10n\Sdk\Model\Language;
use Proximum\Vimeet\Openl10n\Sdk\Model\Resource;

class PushHandler
{
    /** @var string */
    private $configFilePath;

    public function __construct(string $configFilePath)
    {
        $this->configFilePath = $configFilePath;
    }

    public function handle(Push $command): PushResult
    {
        $result = new PushResult();

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
        $projectLocales = array_map(
            static function (Language $language) {
                return $language->getLocale();
            },
            $languages
        );

        $defaultLocale = $project->getDefaultLocale();

        // Retrieve locales option
        $localesToPush = $command->locales;
        $localesToPush = array_unique($localesToPush);
        $pushAllLocale = false;

        // Process locales special cases
        if (\in_array('all', $localesToPush, true)) {
            $localesToPush = $projectLocales;
            $pushAllLocale = true;
        } elseif (false !== $key = array_search('default', $localesToPush, true)) {
            unset($localesToPush[$key]);
            $localesToPush[] = $defaultLocale;
        }

        // Deduplicate values
        $localesToPush = array_unique($localesToPush);

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

        foreach ($fileSets as $fileSet) {
            $files = $fileSet->getFiles();
            $options = $fileSet->getOptions('push');

            foreach ($files as $file) {
                $resourceIdentifier = $file->getPathname(['locale' => $defaultLocale]);
                $locale = $file->getAttribute('locale');

                // Ignore non specified locales
                if (!\in_array($locale, $localesToPush, true) && !$pushAllLocale) {
                    continue;
                }

                // Create locale if non existing
                if (!\in_array($locale, $projectLocales, true)) {
                    try {
                        $result->addLocale($locale);

                        $projectApi->addLanguage($project->getSlug(), $locale);
                        $projectLocales[] = $locale;
                    } catch (BadResponseException $e) {
                        $result->addUnknownLocales($locale);
                        continue;
                    }
                }

                // Retrieve or create resource entity
                if (isset($resources[$resourceIdentifier])) {
                    $resource = $resources[$resourceIdentifier];
                } else {
                    $result->addCreatedFile($resourceIdentifier);

                    $resource = new Resource($project->getSlug());
                    $resource->setPathname($resourceIdentifier);
                    $resourceApi->create($resource);

                    $resources[$resourceIdentifier] = $resource;
                }

                $result->addUploadedFile($file->getRelativePathname());

                $resourceApi->import($resource, $file->getAbsolutePathname(), $locale, $options);
            }
        }

        return $result;
    }
}
