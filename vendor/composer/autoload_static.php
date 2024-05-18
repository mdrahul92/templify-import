<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit8283c873e984087f8020f756403ba3f9
{
    public static $prefixLengthsPsr4 = array (
        'K' => 
        array (
            'KadenceWP\\KadenceStarterTemplates\\' => 34,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'KadenceWP\\KadenceStarterTemplates\\' => 
        array (
            0 => __DIR__ . '/../..' . '/include/resources',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit8283c873e984087f8020f756403ba3f9::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit8283c873e984087f8020f756403ba3f9::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit8283c873e984087f8020f756403ba3f9::$classMap;

        }, null, ClassLoader::class);
    }
}
