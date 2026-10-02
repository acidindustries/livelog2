<?php

namespace AcidIndustries\Livelog\Payloads;

final class MessagePayload extends Payload {
    public function __construct(
        private readonly string $_message,
        ?string $sessionId = null,
        ?string $requestId = null,
        ?array $source = null,
    ) {
        parent::__construct(
            'message',
            $sessionId,
            $requestId,
            $source,
        );
    }

    protected function data(): array {
        return [
            'message' => $this->_message,
        ];
    }
}
