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

class OtpSecret
{
    /** @var string */
    private $dataDir;

    public function __construct($dataDir)
    {
        $this->dataDir = $dataDir;
    }

    /**
     * Get a list of users that have an OTP secret set.
     */
    public function getOtpSecrets()
    {
        $otpSecrets = [];

        $fileList = glob(sprintf('%s/*', $this->dataDir), GLOB_ERR);
        if (false === $fileList) {
            throw new RuntimeException(sprintf('unable to read directory "%s"', $this->dataDir));
        }

        foreach ($fileList as $fileName) {
            $otpSecrets[] = basename($fileName);
        }

        return $otpSecrets;
    }

    /**
     * Get OTP secret for a particular user.
     */
    public function getOtpSecret($userId)
    {
        $otpSecretFile = sprintf('%s/%s', $this->dataDir, $userId);

        return @file_get_contents($otpSecretFile);
    }

    public function set($userId, $otpSecret)
    {
        $otpSecretFile = sprintf('%s/%s', $this->dataDir, $userId);

        // do not allow an override of an existing OTP secret
        if (false !== $this->getOtpSecret($userId)) {
            return false;
        }

        // we have to make sure the directory for writing the file exists, if
        // it does not, we attempt to create it
        if (!file_exists($this->dataDir)) {
            if (false === @mkdir($this->dataDir, 0700, true)) {
                throw new RuntimeException(sprintf('unable to create directory "%s"', $this->dataDir));
            }
        }

        return @file_put_contents($otpSecretFile, $otpSecret);
    }

    public function delete($userId)
    {
        $otpSecretFile = sprintf('%s/%s', $this->dataDir, $userId);

        return @unlink($otpSecretFile);
    }
}
