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

use PHPUnit_Framework_TestCase;
use Otp\Otp;
use Base32\Base32;
use PDO;

class TwoFactorTest extends PHPUnit_Framework_TestCase
{
    /** @var OtpLog */
    private $otpLog;

    public function setUp()
    {
        $db = new PDO('sqlite::memory:');
        $this->otpLog = new OtpLog($db);
        $this->otpLog->initDatabase();
    }

    public function testTwoFactorValid()
    {
        $o = new Otp();
        $otpKey = $o->totp(Base32::decode('QPXDFE7G7VNRR4BH'));

        $c = new TwoFactor(__DIR__, $this->otpLog);
        $c->twoFactor(
            [
                'INSTANCE_ID' => 'vpn.example',
                'POOL_ID' => 'internet',
                'common_name' => 'foo_xyz',
                'username' => 'totp',
                'password' => $otpKey,
            ]
        );
    }

    /**
     * @expectedException \fkooman\VPN\Server\Exception\TwoFactorException
     * @expectedExceptionMessage invalid OTP key
     */
    public function testTwoFactorWrongKey()
    {
        $c = new TwoFactor(__DIR__, $this->otpLog);
        $c->twoFactor(
            [
                'INSTANCE_ID' => 'vpn.example',
                'POOL_ID' => 'internet',
                'common_name' => 'foo_xyz',
                'username' => 'totp',
                'password' => '999999',
            ]
        );
    }

    /**
     * @expectedException \fkooman\VPN\Server\Exception\TwoFactorException
     * @expectedExceptionMessage OTP replayed
     */
    public function testTwoFactorReplay()
    {
        $o = new Otp();
        $otpKey = $o->totp(Base32::decode('QPXDFE7G7VNRR4BH'));

        $c = new TwoFactor(__DIR__, $this->otpLog);
        $c->twoFactor(
            [
                'INSTANCE_ID' => 'vpn.example',
                'POOL_ID' => 'internet',
                'common_name' => 'foo_xyz',
                'username' => 'totp',
                'password' => $otpKey,
            ]
        );
        // replay
        $c->twoFactor(
            [
                'INSTANCE_ID' => 'vpn.example',
                'POOL_ID' => 'internet',
                'common_name' => 'foo_xyz',
                'username' => 'totp',
                'password' => $otpKey,
            ]
        );
    }

    /**
     * @expectedException \fkooman\VPN\Server\Exception\TwoFactorException
     * @expectedExceptionMessage no OTP secret registered
     */
    public function testTwoFactorNotEnrolled()
    {
        $c = new TwoFactor(__DIR__, $this->otpLog);
        $c->twoFactor(
            [
                'INSTANCE_ID' => 'vpn.example',
                'POOL_ID' => 'internet',
                'common_name' => 'bar_xyz',
                'username' => 'totp',
                'password' => '999999',
            ]
        );
    }
}
