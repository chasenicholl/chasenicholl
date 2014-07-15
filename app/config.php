<?php
define('SESSION_NAME', '__replicator2client');

# Conveyor Settings
define('CONVEYOR_DIR', '/Library/MakerBot/');
define('CONVEYOR_ADDRESS', '127.0.0.1');
define('CONVEYOR_PORT', 10002);

# Database
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', 'root');
define('DB_PORT', 3306);
define('DB_DB', 'rep2client');
define('DB_PERSISTENT', true);

# Cachce
define('CACHE_MODELS', true);

# Memcache 
define('MEMCACHE_ON', false);
define('MEMCACHE_HOST' , 'memcache.int.site.net');
define('MEMCACHE_PORT' , 11211);
define('MEMCACHE_EXPIRE', 600);