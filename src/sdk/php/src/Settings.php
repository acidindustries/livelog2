<?php

namespace AcidIndustries\Livelog;

use RangeException;

class Settings {
    private string $_host = 'localhost';
    private int $_port = 8000;

    private const MIN_PORT = 1;
    private const MAX_PORT = 65535;

    public function __construct() {
    }

    public function host(string $hostname): Settings {
        $this->_host = $hostname;

        return $this;
    }

    public function port(int $port): Settings {
        if ($port < self::MIN_PORT || $port > self::MAX_PORT) {
            throw new RangeException('Specified port must be between ' . self::MIN_PORT . ' and ' . self::MAX_PORT);
        }

        $this->_port = $port;

        return $this;
    }

    public function getEndpoint(): string {
        return "http://{$this->_host}:{$this->_port}/api/events";
    }
}
