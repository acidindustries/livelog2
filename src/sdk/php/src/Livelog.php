<?php

namespace AcidIndustries\Livelog;

use AcidIndustries\Livelog\Payloads\ExceptionPayload;
use AcidIndustries\Livelog\Payloads\GroupEndPayload;
use AcidIndustries\Livelog\Payloads\GroupStartPayload;
use AcidIndustries\Livelog\Payloads\MessagePayload;
use AcidIndustries\Livelog\Payloads\Payload;
use AcidIndustries\Livelog\Payloads\RequestEndPayload;
use AcidIndustries\Livelog\Payloads\RequestStartPayload;
use AcidIndustries\Livelog\Payloads\SpacerPayload;
use AcidIndustries\Livelog\Payloads\TimerPayload;
use AcidIndustries\Livelog\Payloads\VariablePayload;
use BadFunctionCallException;
use DateInterval;
use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use Exception;
use JsonException;
use LogicException;

class Livelog {
    private static Settings $_settings;
    private static bool $_isActive = true;
    /** @var Payload[] $_buffers */
    private static array $_queue = [];
    private static array $_timers = [];
    private static ?string $_sessionId = null;
    private static ?string $_requestId = null;
    private static ?DateTimeImmutable $_requestTimer = null;
    private static ?string $_requestMethod = null;
    private static ?string $_requestPath = null;
    private static ?string $_requestStatusCode = null;

    public static function init(?Settings $settings = null) {
        self::$_settings = $settings ?? self::$_settings ?? new Settings();
    }

    public static function config(Settings $settings) {
        self::$_settings = $settings;
    }

    public static function enable() {
        self::$_isActive = true;
    }

    public static function disable() {
        self::$_isActive = false;
    }

    public static function isActive(): bool {
        return self::$_isActive;
    }

    public static function setSessionId(?string $sessionId) {
        self::$_sessionId = $sessionId;
    }

    public static function setRequestId(?string $requestId) {
        self::$_requestId = $requestId;
    }

    private static function enqueue(Payload $payload) {
        self::$_queue[] = $payload;
    }

    public static function message(string $message): ?MessagePayload {
        if (!self::isActive()) {
            return null;
        }

        self::init();

        $payload = new MessagePayload(
            $message,
            self::$_sessionId,
            self::$_requestId,
            self::captureSource(),
        );

        self::enqueue($payload);

        return $payload;
    }

    public static function dump(
        mixed $value,
        ?string $label = null,
    ): ?VariablePayload {
        if (!self::isActive()) {
            return null;
        }

        self::init();

        $payload = new VariablePayload(
            $value,
            $label,
            self::$_sessionId,
            self::$_requestId,
            self::captureSource(),
        );

        self::enqueue($payload);

        return $payload;
    }

    public static function spacer(?string $label = null): ?SpacerPayload {
        if (!self::isActive()) {
            return null;
        }

        self::init();

        $payload = new SpacerPayload(
            $label,
            self::$_sessionId,
            self::$_requestId,
            self::captureSource()
        );

        self::enqueue($payload);

        return $payload;
    }

    public static function groupStart(string $groupName) {
        if (!self::isActive()) {
            return null;
        }

        self::init();

        $payload = new GroupStartPayload(
            $groupName,
            self::$_sessionId,
            self::$_requestId,
            self::captureSource()
        );

        self::enqueue($payload);

        return $payload;
    }

    public static function groupEnd() {
        if (!self::isActive()) {
            return null;
        }

        self::init();

        $payload = new GroupEndPayload(
            self::$_sessionId,
            self::$_requestId,
            self::captureSource()
        );

        self::enqueue($payload);

        return $payload;
    }

    public static function exception(Exception $exception) {
        if (!self::isActive()) {
            return null;
        }

        self::init();

        $payload = new ExceptionPayload(
            $exception,
            self::$_sessionId,
            self::$_requestId
        );

        self::enqueue($payload);

        return $payload;
    }

    public static function requestStart(string $method, string $path, ?string $requestId = null) {
        self::$_requestId = $requestId;

        self::$_requestTimer = new DateTimeImmutable('now', new DateTimeZone('UTC'));

        $payload = new RequestStartPayload(
            $method,
            $path,
            self::$_sessionId,
            $requestId,
        );

        self::enqueue($payload);

        return $payload;
    }

    public static function requestEnd(int $statusCode) {
        $now = new DateTimeImmutable('now', new DateTimeZone('UTC'));

        $payload = new RequestEndPayload(
            $statusCode,
            self::$_requestTimer->diff($now),
            self::$_sessionId,
            self::$_requestId,
        );

        self::enqueue($payload);

        return $payload;
    }

    public static function startTimer(string $name) {
        if (isset(self::$_timers[$name])) {
            throw new LogicException("Timer '{$name}' is already running.");
        }

        self::init();

        self::$_timers[$name] = [
            'sessionId' => self::$_sessionId,
            'requestId' => self::$_requestId,
            'source' => self::captureSource(),
            'startedAt' => new DateTimeImmutable('now', new DateTimeZone('UTC')),
        ];
    }

    public static function stopTimer(string $name) {
        if (!isset(self::$_timers[$name])) {
            throw new LogicException("Timer '{$name}' is not running.");
        }

        $timer = self::$_timers[$name];

        $interval = $timer['startedAt']->diff((new DateTimeImmutable('now', new DateTimeZone('UTC'))));

        unset(self::$_timers[$name]);

        if (self::isActive()) {
            self::enqueue(new TimerPayload(
                $name,
                $interval,
                $timer['sessionId'],
                $timer['requestId'],
                $timer['source'],
            ));
        }

        return $interval;
    }

    public static function timer(string $name, DateInterval $interval) {
        if (!self::isActive()) {
            return null;
        }

        self::init();

        $payload = new TimerPayload(
            $name,
            $interval,
            self::$_sessionId,
            self::$_requestId,
            self::captureSource()
        );

        self::enqueue($payload);

        return $payload;
    }

    public static function ship(): bool {
        if (self::$_queue === []) {
            return true;
        }

        self::init();

        $sent = 0;

        try {
            foreach (self::$_queue as $payload) {
                if (!self::send($payload)) {
                    return false;
                }

                $sent++;
            }

            return true;
        } finally {
            self::$_queue = array_slice(self::$_queue, $sent);
        }
    }

    private static function send(Payload $payload): bool {
        try {
            $json = json_encode($payload, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return false;
        }

        $curl = curl_init(self::$_settings->getEndpoint());

        if ($curl === false) {
            return false;
        }

        $configured = curl_setopt_array($curl, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $json,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept: application/json',
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_CONNECTTIMEOUT_MS => 500,
            CURLOPT_TIMEOUT_MS => 2000,
        ]);

        if (!$configured) {
            return false;
        }

        $response = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);

        return $response !== false
            && $status >= 200
            && $status < 300;
    }

    private static function captureSource(): ?array {
        $caller = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1] ?? [];

        if (!isset($caller['file'])) {
            return null;
        }

        return [
            'file' => $caller['file'],
            'line' => $caller['line'] ?? null,
        ];
    }
}
