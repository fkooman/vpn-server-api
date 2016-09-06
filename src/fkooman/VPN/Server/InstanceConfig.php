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

use fkooman\VPN\Server\Exception\InstanceException;

/**
 * Read the configuration of a particular instance.
 */
class InstanceConfig extends Config
{
    /**
     * Retrieve a configuration object for a pool.
     */
    public function pool($poolId)
    {
        if (!array_key_exists('vpnPools', $this->configData)) {
            throw new InstanceException('missing "vpnPools" in configuration');
        }

        if (!array_key_exists($poolId, $this->configData['vpnPools'])) {
            throw new InstanceException(sprintf('pool "%s" not found in "vpnPools"', $poolId));
        }

        return new PoolConfig($this->configData['vpnPools'][$poolId]);
    }

    /**
     * Retrieve a list of all pools.
     */
    public function pools()
    {
        return array_keys($this->v('vpnPools'));
    }

    public function groupProvider($groupProviderId)
    {
        if (!array_key_exists('groupProviders', $this->configData)) {
            throw new InstanceException('missing "groupProviders" in configuration');
        }

        if (!array_key_exists($groupProviderId, $this->configData['groupProviders'])) {
            return [];
        }

        return $this->configData['groupProviders'][$groupProviderId];
    }

    public function instanceNumber()
    {
        return $this->v('instanceNumber');
    }
}
