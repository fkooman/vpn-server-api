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

class Utils
{
    /**
     * Depending on the prefix we will divide it in a number of nets to
     * balance the load over the processes, it is recommended to use a least
     * a /24.
     *
     * A /24 or 'bigger' will be split in 4 networks, everything 'smaller'
     * will be either be split in 2 networks or remain 1 network.
     */
    public static function getNetCount($prefix)
    {
        switch ($prefix) {
            case 32:    // 1 IP
            case 31:    // 2 IPs
                throw new RuntimeException('not enough available IPs in range');
            case 30:    // 4 IPs (1 usable for client, no splitting)
            case 29:    // 8 IPs (5 usable for clients, no splitting)
                return 1;
            case 28:    // 16 IPs (12 usable for clients)
            case 27:    // 32 IPs
            case 26:    // 64 IPs
            case 25:    // 128 IPs
                return 2;
            case 24:
                return 4;
        }

        return 8;
    }
}
