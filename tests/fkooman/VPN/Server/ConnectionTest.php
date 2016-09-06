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

class ConnectionTest extends PHPUnit_Framework_TestCase
{
    public function testConnect()
    {
        $c = new Connection(__DIR__);
        $c->connect(
            [
                'INSTANCE_ID' => 'vpn.example',
                'POOL_ID' => 'internet',
                'common_name' => 'foo_xyz',
                'time_unix' => 1234567890,
                'ifconfig_pool_remote_ip' => '10.10.10.25',
                'ifconfig_pool_remote_ip6' => 'fd00:1234::25',
            ]
        );
    }

    /**
     * @expectedException \fkooman\VPN\Server\Exception\ConnectionException
     * @expectedExceptionMessage client not allowed, user is disabled
     */
    public function testDisabledUser()
    {
        $c = new Connection(__DIR__);
        $c->connect(
            [
                'INSTANCE_ID' => 'vpn.example',
                'POOL_ID' => 'internet',
                'common_name' => 'bar_xyz',
                'time_unix' => 1234567890,
                'ifconfig_pool_remote_ip' => '10.10.10.25',
                'ifconfig_pool_remote_ip6' => 'fd00:1234::25',
            ]
        );
    }

    /**
     * @expectedException \fkooman\VPN\Server\Exception\ConnectionException
     * @expectedExceptionMessage client not allowed, CN is disabled
     */
    public function testDisabledCommonName()
    {
        $c = new Connection(__DIR__);
        $c->connect(
            [
                'INSTANCE_ID' => 'vpn.example',
                'POOL_ID' => 'internet',
                'common_name' => 'foo_disabled',
                'time_unix' => 1234567890,
                'ifconfig_pool_remote_ip' => '10.10.10.25',
                'ifconfig_pool_remote_ip6' => 'fd00:1234::25',
            ]
        );
    }

    public function testAclIsMember()
    {
        $c = new Connection(__DIR__);
        $c->connect(
            [
                'INSTANCE_ID' => 'vpn.example',
                'POOL_ID' => 'bar',
                'common_name' => 'foo_xyz',
                'time_unix' => 1234567890,
                'ifconfig_pool_remote_ip' => '10.10.10.25',
                'ifconfig_pool_remote_ip6' => 'fd00:1234::25',
            ]
        );
    }

    /**
     * @expectedException \fkooman\VPN\Server\Exception\ConnectionException
     * @expectedExceptionMessage client not allowed, not a member of "all"
     */
    public function testAclIsNoMember()
    {
        $c = new Connection(__DIR__);
        $c->connect(
            [
                'INSTANCE_ID' => 'vpn.example',
                'POOL_ID' => 'bar',
                'common_name' => 'xyz_abc',
                'time_unix' => 1234567890,
                'ifconfig_pool_remote_ip' => '10.10.10.25',
                'ifconfig_pool_remote_ip6' => 'fd00:1234::25',
            ]
        );
    }
}
