<?php
/**
 * @copyright  Copyright (c) Constantin Galbenu xprt64@gmail.com
 * All rights reserved.
 */

$config = [
    'mongoEventStoreDsn' => getenv('MONGO_EVENT_STORE_DSN') ?: "mongodb://localhost:27017/eventStore",
];

return $config;