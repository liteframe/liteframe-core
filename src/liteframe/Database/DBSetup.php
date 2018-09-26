<?php

if (empty($GLOBALS['__rbloaded'])) {
    require_once __DIR__ . '/RedBeanPHP5_1_0/rb.php';

    //RedBean setup
    $driver = config('database.driver', 'mysql');
    switch ($driver) {
        case 'sqlite':
            $file = config("database.sqlite.file");
            if ($file) {
                R::setup("sqlite:$file");
            } else {
                R::setup();
            }
            break;
        default:
            $dbhost = config("database.$driver.host", 'localhost');
            $dbname = config("database.$driver.dbname", 'liteframe');
            $dbuser = config("database.$driver.dbuser", 'root');
            $dbpassword = config("database.$driver.dbpassword", '');
            R::setup("$driver:host={$dbhost};dbname={$dbname}", $dbuser, $dbpassword);
            break;
    }

//    R::setAutoResolve(true);
    
    //with namespace Model
//    define('REDBEAN_MODEL_PREFIX', '\\Models\\');
    //Freeze if auto and on production
    $freezeState = config('database.freeze', 'auto');
    if ($freezeState === 'auto') {
        if (!appIsLocal()) {
            R::freeze();
        }
    } else {
        R::freeze($freezeState);
    }
    
//    R::ext('xdispense', function ($type) {
//        return R::getRedBean()->dispense($type);
//    });

    /**
     * True is RedBeanPHP has been loaded, else false
     * @global string $GLOBALS['__rbloaded']
     * @name $__rbloaded
     */
    $GLOBALS['__rbloaded'] = true;
}
