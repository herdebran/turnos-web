<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit717f5496b4ecd9268472af359bfd30ed
{
    public static $prefixLengthsPsr4 = array (
        'P' => 
        array (
            'PHPMailer\\PHPMailer\\' => 20,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'PHPMailer\\PHPMailer\\' => 
        array (
            0 => __DIR__ . '/..' . '/phpmailer/phpmailer/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit717f5496b4ecd9268472af359bfd30ed::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit717f5496b4ecd9268472af359bfd30ed::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
