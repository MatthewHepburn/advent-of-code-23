<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . 'StandardLib.php';

spl_autoload_register(function ($class) {
   $class = trim($class, '\\');
   $parts = preg_split('/[\\\]+/', $class, 3);
   if (count($parts) < 3) {
       return;
   }
   [$package, $dir, $other] = $parts;
   if ($package !== 'AoC') {
       return;
   }
   if ($dir !== 'Common') {
       return;
   }

   $file = preg_replace('/\\+/', DIRECTORY_SEPARATOR, $other) . '.php';

   include_once __DIR__ . DIRECTORY_SEPARATOR . $file;
});
