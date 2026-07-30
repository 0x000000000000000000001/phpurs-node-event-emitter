<?php

$exports = [];

$exports['new'] = function() {
    return new class {
        public $listeners = [];
        public $maxListeners = 10;
        public function on($event, $cb) { $this->listeners[$event][] = $cb; return $this; }
        public function once($event, $cb) {
            $wrapper = function(...$args) use ($event, $cb, &$wrapper) {
                $this->off($event, $wrapper);
                $cb(...$args);
            };
            $this->on($event, $wrapper);
            return $this;
        }
        public function off($event, $cb) {
            if (isset($this->listeners[$event])) {
                $this->listeners[$event] = array_filter($this->listeners[$event], function($l) use ($cb) { return $l !== $cb; });
            }
            return $this;
        }
        public function emit($event, ...$args) {
            $called = false;
            foreach (($this->listeners[$event] ?? []) as $cb) { $cb(...$args); $called = true; }
            return $called;
        }
        public function eventNames() { return array_keys($this->listeners); }
        public function getMaxListeners() { return $this->maxListeners; }
        public function listenerCount($event) { return count($this->listeners[$event] ?? []); }
        public function setMaxListeners($n) { $this->maxListeners = $n; return $this; }
        public function prependListener($event, $cb) { 
            if (!isset($this->listeners[$event])) $this->listeners[$event] = [];
            array_unshift($this->listeners[$event], $cb); 
            return $this;
        }
        public function prependOnceListener($event, $cb) {
            $wrapper = function(...$args) use ($event, $cb, &$wrapper) {
                $this->off($event, $wrapper);
                $cb(...$args);
            };
            $this->prependListener($event, $wrapper);
            return $this;
        }
    };
};

$exports['unsafeEmitFn'] = function($emitter) {
    return function(...$args) use ($emitter) {
        if (method_exists($emitter, 'emit')) return $emitter->emit(...$args);
        elseif (isset($emitter->emit) && is_callable($emitter->emit)) { $f = $emitter->emit; return $f(...$args); }
        return false;
    };
};

$exports['eventNamesImpl'] = function($emitter) {
    if (method_exists($emitter, 'eventNames')) return $emitter->eventNames();
    return [];
};

$exports['symbolOrStr'] = function($left, $right, $sym) {
    if (is_string($sym) && strpos($sym, "Symbol(") === 0) {
        return $left($sym);
    }
    return $right($sym);
};

$exports['getMaxListenersImpl'] = function($emitter) {
    if (method_exists($emitter, 'getMaxListeners')) return $emitter->getMaxListeners();
    return 10;
};

$exports['listenerCountImpl'] = function($emitter, $eventName) {
    if (method_exists($emitter, 'listenerCount')) return $emitter->listenerCount($eventName);
    return 0;
};

$exports['unsafeOff'] = function($emitter, $eventName, $cb) {
    if (method_exists($emitter, 'off')) $emitter->off($eventName, $cb);
    elseif (method_exists($emitter, 'removeListener')) $emitter->removeListener($eventName, $cb);
};

$exports['unsafeOn'] = function($emitter, $eventName, $cb) {
    if (method_exists($emitter, 'on')) {
        $emitter->on($eventName, $cb);
    } elseif (isset($emitter->on) && is_callable($emitter->on)) {
        $f = $emitter->on;
        $f($eventName, $cb);
    }
};

$exports['unsafeOnce'] = function($emitter, $eventName, $cb) {
    if (method_exists($emitter, 'once')) {
        $emitter->once($eventName, $cb);
    } elseif (isset($emitter->once) && is_callable($emitter->once)) {
        $f = $emitter->once;
        $f($eventName, $cb);
    }
};

$exports['unsafePrependListener'] = function($emitter, $eventName, $cb) {
    if (method_exists($emitter, 'prependListener')) {
        $emitter->prependListener($eventName, $cb);
    } else {
        $exports['unsafeOn']($emitter, $eventName, $cb);
    }
};

$exports['unsafePrependOnceListener'] = function($emitter, $eventName, $cb) {
    if (method_exists($emitter, 'prependOnceListener')) {
        $emitter->prependOnceListener($eventName, $cb);
    } else {
        $exports['unsafeOnce']($emitter, $eventName, $cb);
    }
};

$exports['setMaxListenersImpl'] = function($emitter, $max) {
    if (method_exists($emitter, 'setMaxListeners')) $emitter->setMaxListeners($max);
};

return $exports;
