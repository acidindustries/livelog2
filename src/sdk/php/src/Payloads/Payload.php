<?php

namespace AcidIndustries\Livelog\Payloads;

use DateTimeImmutable;
use DateTimeZone;
use JsonSerializable;
use Ramsey\Uuid\Uuid;

abstract class Payload implements JsonSerializable {
    private readonly string $_id;
    private readonly string $_timestamp;

    public function __construct(
        private readonly string $_type,
        private readonly ?string $_sessionId = null,
        private readonly ?string $_requestId = null,
        private readonly ?array $_source = null,
    ) {
        $this->_id = Uuid::uuid7()->toString();

        $this->_timestamp = (
            new DateTimeImmutable('now', new DateTimeZone('UTC')))
            ->format('Y-m-d\TH:i:s.u\Z');
    }

    abstract protected function data(): array;

    final public function jsonSerialize(): mixed {
        return [
            'type' => $this->_type,
            'version' => 1,
            'id' => $this->_id,
            'timestamp' => $this->_timestamp,
            'sessionId' => $this->_sessionId,
            'requestId' => $this->_requestId,
            'source' => $this->_source,
        ] + $this->data();
    }
}
