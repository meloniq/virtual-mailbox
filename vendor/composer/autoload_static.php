<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit15ad3a0d41fb6330600f337878e78fbb
{
    public static $prefixLengthsPsr4 = array (
        'M' => 
        array (
            'Meloniq\\VirtualMailbox\\' => 23,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Meloniq\\VirtualMailbox\\' => 
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
            $loader->prefixLengthsPsr4 = ComposerStaticInit15ad3a0d41fb6330600f337878e78fbb::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit15ad3a0d41fb6330600f337878e78fbb::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit15ad3a0d41fb6330600f337878e78fbb::$classMap;

        }, null, ClassLoader::class);
    }
}
