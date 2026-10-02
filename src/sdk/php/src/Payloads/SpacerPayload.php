<?php

namespace AcidIndustries\Livelog\Payloads;

final class SpacerPayload extends Payload {
    public function __construct(
        private readonly ?string $_label = null,
        ?string $sessionId = null,
        ?string $requestId = null,
        ?array $source = null
    ) {
        parent::__construct(
            'spacer',
            $sessionId,
            $requestId,
            $source
        );
    }

    protected function data(): array {
        return [
            'label' => $this->_label,
        ];
    }
}
