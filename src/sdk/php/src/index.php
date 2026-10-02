<?php

require __DIR__ . '/../vendor/autoload.php';

use AcidIndustries\Livelog\Livelog;
use AcidIndustries\Livelog\Settings;

Livelog::init((new Settings())->port(5092));
Livelog::setSessionId('debug-session');
Livelog::setRequestId('request-123');

$payloadStart = Livelog::requestStart('POST', '/imile');
$payloadEnd = Livelog::requestEnd(200);

echo json_encode($payloadStart, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
echo json_encode($payloadEnd, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);

if (!Livelog::ship()) {
    error_log('Livelog delivery failed; unsent events');
}
