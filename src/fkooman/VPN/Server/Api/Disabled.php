<?php
/**
 *  Copyright (C) 2016 SURFnet
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as
 *  published by the Free Software Foundation, either version 3 of the
 *  License, or (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
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
