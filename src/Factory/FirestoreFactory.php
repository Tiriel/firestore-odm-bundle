<?php

namespace Tiriel\FirestoreOdmBundle\Factory;

use Google\Cloud\Firestore\FirestoreClient;

class FirestoreFactory
{
    public static function create(string|array $config): FirestoreClient
    {
        $gcConfig = [];
        if (isset($config['project_name'])) {
            $gcConfig['projectId'] = $config['project_name'];
        }

        $gcConfig['keyFile'] = match (\is_array($config['service_account'])) {
            true => $config['service_account'],
            false => str_starts_with($config['service_account'], '{')
                ? \json_decode($config['service_account'], true)
                : \json_decode(\file_get_contents($config['service_account']), true)
        };

        return new FirestoreClient($gcConfig);
    }
}
