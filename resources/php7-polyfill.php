<?php
/**
 * Incomplete polyfill for php7. Currently only ensures that the EngineExcepton exists so we can catch fatal errors from
 * PHP >= 7.0. On versions prior to 7.0 we will never have to deal with this exception since the application would just
 * crash with a fatal error <:-)
 */
if (!class_exists('EngineException')) {
    // Enable the use of engine exception on applications  < 7.0
    class EngineException extends Exception {
    }
}