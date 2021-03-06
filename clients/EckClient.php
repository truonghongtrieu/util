<?php

namespace go1\clients;

use go1\util\user\UserHelper;
use GuzzleHttp\Client;

class EckClient
{
    private $client;
    private $url;
    private $jwt = UserHelper::ROOT_JWT;

    public function __construct(Client $client, string $url)
    {
        $this->client = $client;
        $this->url = $url;
    }

    public function fields(string $instance, string $entityType) : array
    {
        $data = $this->client->get("{$this->url}/fields/{$instance}/{$entityType}?jwt={$this->jwt}")->getBody()->getContents();
        $data = json_decode($data, true);

        $fields = [];
        if (!empty($data)) {
            foreach ($data['fields'] as $fieldName => $item) {
                $fields[$fieldName] = [
                    'label'     => $item['label'],
                    'type'      => $item['type'],
                    'enum'      => $item['enum'],
                    'mandatory' => $item['mandatory'],
                    'published' => $item['published']
                ];
            }
        }

        return $fields;
    }

    public function create(string $instance, string $entityType, int $entityId, array $fields)
    {
        $eckUrl = "{$this->url}/entity/{$instance}/{$entityType}/{$entityId}?jwt={$this->jwt}";
        $this->client->post($eckUrl, ['json' => ['fields' => $fields]]);
    }

    public function update(string $instance, string $entityType, int $entityId, array $fields)
    {
        $eckUrl = "{$this->url}/entity/{$instance}/{$entityType}/{$entityId}?jwt={$this->jwt}";
        return $this->client->put($eckUrl, ['json' => ['fields' => $fields]]);
    }

    /**
     * Get the entity's custom fields data.
     *
     * @param string $instance the portal name
     * @param string $entityType the entity type (e.g.: 'account')
     * @param string $entityId the entity's account id (i.e.: the user's account id)
     * @return array a list of custom fields for the entity.
     */
    public function getEntityData(string $instance, string $entityType, int $entityId)
    {
        $eckUrl = "{$this->url}/entity/{$instance}/{$entityType}/{$entityId}?jwt={$this->jwt}";
        $data = $this->client->get($eckUrl)->getBody()->getContents();
        return json_decode($data, true);
    }
}
