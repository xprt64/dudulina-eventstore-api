<?php
/**
 * @copyright  Copyright (c) Constantin Galbenu xprt64@gmail.com
 * All rights reserved.
 */

$eventStoreDsn = getenv('MONGO_EVENT_STORE_DSN');
if (preg_match('#\{/run/secrets/MONGODB_CREDENTIALS\}#ims', $eventStoreDsn)) {
    $eventStoreDsn = str_replace('{/run/secrets/MONGODB_CREDENTIALS}', rtrim(file_get_contents('/run/secrets/MONGODB_CREDENTIALS'), "\n"), $eventStoreDsn);
}
$config = [
    'mongoEventStoreDsn' => $eventStoreDsn ?: "mongodb://localhost:27017/eventStore",
];

return $config;