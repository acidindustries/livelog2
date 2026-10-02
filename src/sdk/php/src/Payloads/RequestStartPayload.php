<?php

namespace AcidIndustries\Livelog\Payloads;

final class RequestStartPayload extends Payload {
    public function __construct(
        private readonly string $_method,
        private readonly string $_path,
        ?string $sessionId = null,
        ?string $requestId = null,
        ?array $source = null
    ) {
        return parent::__construct(
            'request-start',
            $sessionId,
            $requestId,
            $source
        );
    }

    protected function data(): array {
        return [
            'method' => $this->_method,
            'path' => $this->_path,
        ];
    }
}
