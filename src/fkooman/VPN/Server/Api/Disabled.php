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
namespace fkooman\VPN\Server\Api;

use RuntimeException;

/**
 * Class used to enable, disable and enumerate disabled users or common names,
 * it is used for both.
 */
class Disabled
{
    /** @var string */
    private $dataDir;

    public function __construct($dataDir)
    {
        $this->dataDir = $dataDir;
    }

    public function getAll()
    {
        $disabledList = [];

        $fileList = glob(sprintf('%s/*', $this->dataDir), GLOB_ERR);
        if (false === $fileList) {
            throw new RuntimeException(sprintf('unable to read directory "%s"', $this->dataDir));
        }

        foreach ($fileList as $fileName) {
            $disabledList[] = basename($fileName);
        }

        return $disabledList;
    }

    public function isDisabled($t)
    {
        $disableFile = sprintf('%s/%s', $this->dataDir, $t);

        // we cannot distinguish between a non existing file or a failure to
        // read at the location where the file should be...
        return @file_exists($disableFile);
    }

    public function enable($t)
    {
        return @unlink(sprintf('%s/%s', $this->dataDir, $t));
    }

    public function disable($t)
    {
        // we have to make sure the directory for writing the file exists, if
        // it does not, we attempt to create it
        if (!file_exists($this->dataDir)) {
            if (false === @mkdir($this->dataDir, 0700, true)) {
                throw new RuntimeException(sprintf('unable to create directory "%s"', $this->dataDir));
            }
        }

        return @file_put_contents(sprintf('%s/%s', $this->dataDir, $t), time());
    }
}
