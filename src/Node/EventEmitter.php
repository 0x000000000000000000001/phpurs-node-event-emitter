<?php

$exports = [];

$exports['unsafeOn'] = function($emitter, $event, $listener) {
    if (method_exists($emitter, 'on')) {
        $emitter->on($event, $listener);
    } elseif (isset($emitter->on) && is_callable($emitter->on)) {
        $f = $emitter->on;
        $f($event, $listener);
    }
};

return $exports;
