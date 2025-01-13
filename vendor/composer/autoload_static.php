<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit7043f6337dfd8183e16e5fea26c38324
{
    public static $prefixLengthsPsr4 = array (
        'A' => 
        array (
            'App\\Content\\' => 12,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'App\\Content\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit7043f6337dfd8183e16e5fea26c38324::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit7043f6337dfd8183e16e5fea26c38324::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit7043f6337dfd8183e16e5fea26c38324::$classMap;

        }, null, ClassLoader::class);
    }
}
