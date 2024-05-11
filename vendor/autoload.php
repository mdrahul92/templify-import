<?php
<<<<<<< HEAD

// autoload.php @generated by Composer

if (PHP_VERSION_ID < 50600) {
    echo 'Composer 2.3.0 dropped support for autoloading on PHP <5.6 and you are running '.PHP_VERSION.', please upgrade PHP or use Composer 2.2 LTS via "composer self-update --2.2". Aborting.'.PHP_EOL;
    exit(1);
}

require_once __DIR__ . '/composer/autoload_real.php';

return ComposerAutoloaderInit8283c873e984087f8020f756403ba3f9::getLoader();
=======
// autoload.php @generated by Strauss

if ( file_exists( __DIR__ . '/autoload-classmap.php' ) ) {
    $class_map = include __DIR__ . '/autoload-classmap.php';
    if ( is_array( $class_map ) ) {
        spl_autoload_register(
            function ( $classname ) use ( $class_map ) {
                if ( isset( $class_map[ $classname ] ) && file_exists( $class_map[ $classname ] ) ) {
                    require_once $class_map[ $classname ];
                }
            }
        );
    }
    unset( $class_map, $strauss_src );
}

if ( file_exists( __DIR__ . '/autoload-files.php' ) ) {
    require_once __DIR__ . '/autoload-files.php';
}
>>>>>>> origin/master
