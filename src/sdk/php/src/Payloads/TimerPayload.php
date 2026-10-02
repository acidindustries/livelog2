<?php

namespace AcidIndustries\Livelog\Payloads;

use DateInterval;

final class TimerPayload extends Payload {
    private readonly string $_duration;

    public function __construct(
        private readonly string $_name,
        DateInterval $interval,
        ?string $sessionId = null,
        ?string $requestId = null,
        ?array $source = null
    ) {
        $this->_duration = $interval->format('%H:%I:%S.%F');

        return parent::__construct(
            'timer',
            $sessionId,
            $requestId,
            $source
        );
    }

    protected function data(): array {
        return [
            'name' => $this->_name,
            'duration' => $this->_duration,
        ];
    }
}
