<?php

namespace AcidIndustries\Livelog\Payloads;

use Throwable;

final class ExceptionPayload extends Payload {
    private readonly string $_exceptionType;
    private readonly string $_message;
    private readonly string $_stackTrace;

    public function __construct(
        Throwable $exception,
        ?string $sessionId = null,
        ?string $requestId = null,
    ) {
        parent::__construct(
            'exception',
            $sessionId,
            $requestId,
            [
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ],
        );

        $this->_exceptionType = $exception::class;
        $this->_message = $exception->getMessage();
        $this->_stackTrace = $exception->getTraceAsString();
    }

    protected function data(): array {
        return [
            'exceptionType' => $this->_exceptionType,
            'message' => $this->_message,
            'stackTrace' => $this->_stackTrace,
        ];
    }
}
