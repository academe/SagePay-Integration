<?php namespace Academe\SagePayMsg;

/**
 * Register PSR-4 autoloader for projects not using composer or an
 * alternative autoloader.
 */

spl_autoload_register(function($class) {
    if (stripos($class, __NAMESPACE__) === 0) {
        @include(
            __DIR__
            . '/src'
            . str_replace('\\', '/', substr($class, strlen(__NAMESPACE__)))
            . '.php'
        );
    }
});
