<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitb0254a0bc94d132cf2ad96c9464f1856
{
    public static $prefixLengthsPsr4 = array (
        'S' => 
        array (
            'Stripe\\' => 7,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Stripe\\' => 
        array (
            0 => __DIR__ . '/..' . '/stripe/stripe-php/lib',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitb0254a0bc94d132cf2ad96c9464f1856::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitb0254a0bc94d132cf2ad96c9464f1856::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitb0254a0bc94d132cf2ad96c9464f1856::$classMap;

        }, null, ClassLoader::class);
    }
}
