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
