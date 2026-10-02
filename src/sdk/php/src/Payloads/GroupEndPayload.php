<?php

namespace AcidIndustries\Livelog\Payloads;

final class GroupEndPayload extends Payload {
    public function __construct(
        ?string $sessionId = null,
        ?string $requestId = null,
        ?array $source = null
    ) {
        parent::__construct(
            'group-end',
            $sessionId,
            $requestId,
            $source
        );
    }

    protected function data(): array {
        return [];
    }
}
