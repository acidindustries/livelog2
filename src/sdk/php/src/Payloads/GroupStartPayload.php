<?php

namespace AcidIndustries\Livelog\Payloads;

final class GroupStartPayload extends Payload {
    public function __construct(
        private readonly string $_name,
        ?string $sessionId = null,
        ?string $requestId = null,
        ?array $source = null,
    ) {
        parent::__construct(
            'group-start',
            $sessionId,
            $requestId,
            $source
        );
    }

    protected function data(): array {
        return [
            'name' => $this->_name,
        ];
    }
}
