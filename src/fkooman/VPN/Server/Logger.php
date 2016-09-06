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

use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;
use RuntimeException;

class Logger extends AbstractLogger
{
    public function __construct($ident)
    {
        if (false === openlog($ident, LOG_PERROR | LOG_ODELAY, LOG_USER)) {
            throw new RuntimeException('unable to open syslog');
        }
    }

    public function log($level, $message, array $context = array())
    {
        // convert level to syslog level
        $syslogPriority = self::levelToPriority($level);

        // we ignore the context for now
        syslog($syslogPriority, $message);
    }

    private static function levelToPriority($level)
    {
        switch ($level) {
            case LogLevel::EMERGENCY:
                return LOG_EMERG;
            case LogLevel::ALERT:
                return LOG_ALERT;
            case LogLevel::CRITICAL:
                return LOG_CRIT;
            case LogLevel::ERROR:
                return LOG_ERR;
            case LogLevel::WARNING:
                return LOG_WARNING;
            case LogLevel::NOTICE:
                return LOG_NOTICE;
            case LogLevel::INFO:
                return LOG_INFO;
            case LogLevel::DEBUG:
                return LOG_DEBUG;
            default:
                throw new RuntimeException('unknown log level');
        }
    }

    public function __destruct()
    {
        if (false === closelog()) {
            throw new RuntimeException('unable to close syslog');
        }
    }
}
