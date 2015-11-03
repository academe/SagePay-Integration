<?php namespace Academe\SagePayMsg;

/**
 * Register autoloader for projects not using composer.
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
