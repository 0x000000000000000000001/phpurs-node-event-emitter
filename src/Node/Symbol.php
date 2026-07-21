<?php
$exports['showSymbolImpl'] = function($s) { return (string)$s; };
$exports['forImpl'] = function($s) { return "Symbol($s)"; };
$exports['keyForImpl'] = function($s) { return $s; };
return $exports;
