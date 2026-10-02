<?php

namespace AcidIndustries\Livelog\Payloads;

use DateInterval;

final class RequestEndPayload extends Payload {
    public function __construct(
        private readonly int $_statusCode,
        private readonly DateInterval $_requestDuration,
        ?string $sessionId = null,
        ?string $requestId = null,
        ?array $source = null
    ) {
        return parent::__construct(
            'request-end',
            $sessionId,
            $requestId,
            $source
        );
    }

    protected function data(): array {
        return [
            'statusCode' => $this->_statusCode,
            'duration' => $this->_requestDuration->format('%H:%I:%S.%F'),
        ];
    }
}
