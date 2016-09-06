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

use Base32\Base32;
use Otp\Otp;
use RuntimeException;
use fkooman\VPN\Server\Exception\TwoFactorException;

class TwoFactor
{
    /** @var string */
    private $baseDir;

    /** @var OtpLog */
    private $otpLog;

    public function __construct($baseDir, OtpLog $otpLog)
    {
        $this->baseDir = $baseDir;
        $this->otpLog = $otpLog;
    }

    public function twoFactor(array $envData)
    {
        $userId = self::getUserId($envData['common_name']);

        // use username field to specify OTP type, for now we only support 'totp'
        $otpType = $envData['username'];
        if ('totp' !== $otpType) {
            throw new TwoFactorException('invalid OTP type specified in username field');
        }

        $otpKey = $envData['password'];
        // validate the OTP key
        if (0 === preg_match('/^[0-9]{6}$/', $otpKey)) {
            throw new TwoFactorException('invalid OTP key format specified');
        }

        $dataDir = sprintf('%s/data/%s', $this->baseDir, $envData['INSTANCE_ID']);

        if (false === $otpSecret = @file_get_contents(sprintf('%s/users/otp_secrets/%s', $dataDir, $userId))) {
            throw new TwoFactorException('no OTP secret registered');
        }

        $otp = new Otp();
        if ($otp->checkTotp(Base32::decode($otpSecret), $otpKey)) {
            if (false === $this->otpLog->record($userId, $otpKey, time())) {
                throw new TwoFactorException('OTP replayed');
            }
        } else {
            throw new TwoFactorException('invalid OTP key');
        }
    }

    private static function getUserId($commonName)
    {
        if (false === $uPos = strpos($commonName, '_')) {
            throw new RuntimeException('unable to extract userId from commonName');
        }

        return substr($commonName, 0, $uPos);
    }
}
