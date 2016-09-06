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

use fkooman\VPN\Server\Exception\ConfigException;

/**
 * Read configuration.
 */
class Config
{
    /** @var array */
    protected $configData;

    public function __construct(array $configData)
    {
        $this->configData = $configData;
    }

    public static function fromFile($configFile)
    {
        // XXX handle unreadable file
        $parsedConfig = @yaml_parse_file($configFile);

        if (!is_array($parsedConfig)) {
            throw new ConfigException('invalid configuration file format');
        }

        return new static($parsedConfig);
    }

    public function v($key, $defaultValue = null)
    {
        if (array_key_exists($key, $this->configData)) {
            return $this->configData[$key];
        }

        if (is_null($defaultValue)) {
            throw new ConfigException(sprintf('missing configuration field "%s"', $key));
        }

        return $defaultValue;
    }

    public function toArray()
    {
        return $this->configData;
    }
}
