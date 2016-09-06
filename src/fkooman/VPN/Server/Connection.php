<?php
/**
 * Copyright 2016 François Kooman <fkooman@tuxed.net>.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
namespace fkooman\VPN\Server;

use fkooman\VPN\Server\Exception\ConnectionException;
use RuntimeException;

class Connection
{
    /** @var string */
    private $baseDir;

    public function __construct($baseDir)
    {
        $this->baseDir = $baseDir;
    }

    public function connect(array $envData)
    {
        $userId = self::getUserId($envData['common_name']);

        $dataDir = sprintf('%s/data/%s', $this->baseDir, $envData['INSTANCE_ID']);

        // is the user account disabled?
        if (@file_exists(sprintf('%s/users/disabled/%s', $dataDir, $userId))) {
            throw new ConnectionException('client not allowed, user is disabled');
        }

        // is the common name disabled?
        if (@file_exists(sprintf('%s/common_names/disabled/%s', $dataDir, $envData['common_name']))) {
            throw new ConnectionException('client not allowed, CN is disabled');
        }

        $configDir = sprintf('%s/config/%s', $this->baseDir, $envData['INSTANCE_ID']);

        // read the instance/pool configuration
        $instanceConfig = InstanceConfig::fromFile(
            sprintf('%s/config.yaml', $configDir)
        );

        // is the ACL enabled?
        if ($instanceConfig->pool($envData['POOL_ID'])->v('enableAcl', false)) {
            $aclGroupProvider = $instanceConfig->pool($envData['POOL_ID'])->v('aclGroupProvider');
            $groupProviderConfig = $instanceConfig->groupProvider($aclGroupProvider);
            $groupProviderClass = sprintf('fkooman\VPN\Server\GroupProvider\%s', $aclGroupProvider);
            $groupProvider = new $groupProviderClass($groupProviderConfig);
            $aclGroupList = $instanceConfig->pool($envData['POOL_ID'])->v('aclGroupList', []);

            if (false === self::isMember($groupProvider->getGroups($userId), $aclGroupList)) {
                throw new ConnectionException(sprintf('client not allowed, not a member of "%s"', implode(',', $aclGroupList)));
            }
        }
    }

    private static function getUserId($commonName)
    {
        if (false === $uPos = strpos($commonName, '_')) {
            throw new RuntimeException('unable to extract userId from commonName');
        }

        return substr($commonName, 0, $uPos);
    }

    private static function isMember(array $memberOf, array $aclGroupList)
    {
        // one of the groups must be listed in the pool ACL list
        foreach ($memberOf as $memberGroup) {
            if (in_array($memberGroup['id'], $aclGroupList)) {
                return true;
            }
        }

        return false;
    }
}
