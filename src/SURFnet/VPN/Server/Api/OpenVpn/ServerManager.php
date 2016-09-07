<?php
/**
 *  Copyright (C) 2016 SURFnet.
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
namespace SURFnet\VPN\Server\Api\OpenVpn;

use Psr\Log\LoggerInterface;
use SURFnet\VPN\Server\Api\OpenVpn\Exception\ManagementSocketException;

/**
 * Manage all OpenVPN processes controlled by this service.
 */
class ServerManager
{
    /** @var array */
    private $poolList;

    /** @var ManagementSocketInterface */
    private $managementSocket;

    /** @var \Psr\Log\LoggerInterface */
    private $logger;

    public function __construct(array $poolList, ManagementSocketInterface $managementSocket, LoggerInterface $logger)
    {
        $this->poolList = $poolList;
        $this->managementSocket = $managementSocket;
        $this->logger = $logger;
    }

    /**
     * Get the connection information about connected clients.
     */
    public function connections()
    {
        $clientConnections = [];
        // loop over all pools
        foreach ($this->poolList as $pool) {
            $poolConnections = [];
            // loop over all processes
            foreach ($pool->getInstances() as $i => $instance) {
                // add all connections from this instance to poolConnections
                try {
                    // open the socket connection
                    $this->managementSocket->open(
                        sprintf(
                            'tcp://%s:%d',
                            $pool->getManagementIp()->getAddress(),
                            11940 + $i
                        )
                    );
                    $poolConnections = array_merge(
                        $poolConnections,
                        StatusParser::parse($this->managementSocket->command('status 2'))
                    );
                    // close the socket connection
                    $this->managementSocket->close();
                } catch (ManagementSocketException $e) {
                    // we log the error, but continue with the next instance
                    $this->logger->error(
                        sprintf(
                            'error with socket "%s:%s", message: "%s"',
                            $pool->getManagementIp()->getAddress(),
                            11940 + $i,
                            $e->getMessage()
                        )
                    );
                }
            }
            // we add the poolConnections to the clientConnections array
            $clientConnections[] = ['id' => $pool->getId(), 'connections' => $poolConnections];
        }

        return $clientConnections;
    }

    /**
     * Disconnect all clients with this CN from all pools and instances
     * managed by this service.
     *
     * @param string $commonName the CN to kill
     */
    public function kill($commonName)
    {
        $clientsKilled = 0;
        // loop over all pools
        foreach ($this->pools as $pool) {
            // loop over all instances
            foreach ($pool->getInstances() as $i => $instance) {
                // add all kills from this instance to poolKills
                try {
                    // open the socket connection
                    $this->managementSocket->open(
                        sprintf(
                            'tcp://%s:%d',
                            $pool->getManagementIp()->getAddress(),
                            11940 + $i
                        )
                    );

                    $response = $this->managementSocket->command(sprintf('kill %s', $commonName));
                    if (0 === strpos($response[0], 'SUCCESS: ')) {
                        ++$clientsKilled;
                    }
                    // close the socket connection
                    $this->managementSocket->close();
                } catch (ManagementSocketException $e) {
                    // we log the error, but continue with the next instance
                    $this->logger->error(
                        sprintf(
                            'error with socket "%s:%s", message: "%s"',
                            $pool->getManagementIp()->getAddress(),
                            11940 + $i,
                            $e->getMessage()
                        )
                    );
                }
            }
        }

        return 0 !== $clientsKilled;
    }
}
