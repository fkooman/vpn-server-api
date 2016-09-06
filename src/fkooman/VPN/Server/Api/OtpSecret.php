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
