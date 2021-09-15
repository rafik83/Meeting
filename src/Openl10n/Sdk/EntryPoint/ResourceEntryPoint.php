<?php

namespace Proximum\Vimeet\Openl10n\Sdk\EntryPoint;

use Proximum\Vimeet\Openl10n\Sdk\Model\Project;
use Proximum\Vimeet\Openl10n\Sdk\Model\Resource;

class ResourceEntryPoint extends AbstractEntryPoint
{
    public function getName()
    {
        return 'resource';
    }

    /**
     * @param Project $project
     *
     * @return Resource[]
     */
    public function findByProject(Project $project): array
    {
        $results = json_decode(
            $this->getClient()->get(
                'resources',
                [
                    'query' => ['project' => $project->getSlug()]
                ]
            )->getBody(),
            true
        );

        $resources = array();
        foreach ($results as $result) {
            $resource = new Resource($result['project']);
            $resource->setId($result['id']);
            $resource->setPathname($result['pathname']);

            $resources[] = $resource;
        }

        return $resources;
    }

    public function get($id)
    {
        $result = json_decode($this->getClient()->get('resource/'.$id)->getBody(), true);

        $resource = new Resource($result['project']);
        $resource->setId($result['id']);
        $resource->setPathname($result['pathname']);

        return $resource;
    }

    public function create(Resource $resource)
    {
        $response = json_decode(
            $this->getClient()->post(
                'resources',
                [
                    'headers' => [
                        'Content-Type' => 'application/json'
                    ],
                    'body' => json_encode([
                        'project' => $resource->getProjectSlug(),
                        'pathname' => $resource->getPathname(),
                    ]),
                ]
            )->getBody(),
            true
        );

        $resource->setId($response['id']);
    }

    public function update(Resource $resource)
    {
        $this->getClient()->post('resources/'.$resource->getId(), [
            'headers' => [
                'Content-Type' => 'application/json'
            ],
            'body' => json_encode([
                'pathname' => $resource->getPathname(),
            ]),
        ]);
    }

    public function delete(Resource $resource)
    {
        $this->getClient()->delete('resources/'.$resource->getId());
    }

    /**
     * Note : this method had to be changed from the original openl10n-cli project.
     * Guzzle 'form_params' mode changed to 'multipart' because :
     *  - resource from "fopen" wasn't support (file wasn't sent)
     *  - when "fopen" replaced by "file_get_contents", open10n server returns 500 status code
     */
    public function import(Resource $resource, $filepath, $locale, array $options = array())
    {
        $postData = [
            'locale' => $locale,
            'file' => fopen($filepath, 'r'),
            'options' => $options,
        ];

        $formattedPostData = $this->getMultipartFormattedData($postData);

        $this->getClient()->post('resources/'.$resource->getId().'/import', [
            'multipart' => $formattedPostData,
        ]);
    }

    public function export(Resource $resource, $locale, array $options = array(), $format = null)
    {
        $response = $this->getClient()->get('resources/'.$resource->getId().'/export', [
            'query' => [
                'locale' => $locale,
                'format' => $format,
                'options' => $options,
            ],
        ]);

        return $response->getBody();
    }

    /**
     * Note : copied from https://stackoverflow.com/a/41602399
     *
     * @param array $data
     *
     * @return array
     */
    private function getMultipartFormattedData(array $data): array
    {
        $output = [];

        foreach ($data as $key => $value) {
            if (!\is_array($value)) {
                $output[] = ['name' => $key, 'contents' => $value];
                continue;
            }

            foreach ($value as $multiKey => $multiValue) {
                $multiName = $key . '[' . $multiKey . ']'
                    . (\is_array($multiValue) ? '[' . key($multiValue) . ']' : '')
                    . '';
                $output[] = [
                    'name' => $multiName,
                    'contents' => \is_array($multiValue) ? reset($multiValue) : $multiValue,
                ];
            }
        }

        return $output;
    }
}
