<?php

$exports = [];

$exports['unsafeOn'] = function($emitter, $event, $listener) {
    if (method_exists($emitter, 'on')) {
        $emitter->on($event, $listener);
    }
};

return $exports;
