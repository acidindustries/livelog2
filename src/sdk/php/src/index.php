<?php

require __DIR__ . '/../vendor/autoload.php';

use AcidIndustries\Livelog\Livelog;
use AcidIndustries\Livelog\Settings;

Livelog::init((new Settings())->port(5092));
Livelog::setSessionId('debug-session');
Livelog::setRequestId('request-123');

Livelog::startTimer('timer');
// Livelog::timer('External', new DateInterval('PT3S'));
Livelog::stopTimer('timer');
if (!Livelog::ship()) {
    error_log('Livelog delivery failed; unsent events');
}
