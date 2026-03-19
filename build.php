<?php

require_once __DIR__ . '/vendor/autoload.php';

use MatthiasMullie\Minify;

$cssFiles = [
  __DIR__ . '/public/css/style.css',
  __DIR__ . '/public/css/users/courses/viewCourses.css',
  __DIR__ . '/public/css/users/profile.css',
  __DIR__ . '/public/css/auth/forgotPassword.css',
  __DIR__ . '/public/css/auth/confirm.css',
  __DIR__ . '/public/css/layouts/header.css',
  __DIR__ . '/public/css/auth/login.css',
  __DIR__ . '/public/css/auth/register.css',
  __DIR__ . '/public/css/auth/resetPasswordPage.css',
  __DIR__ . '/public/css/auth/verify2fa.css',
];

$jsFiles = [
  __DIR__ . '/public/js/users/profile.js',
  __DIR__ . '/public/js/forgotPassword.js',
  __DIR__ . '/public/js/header.js',
  __DIR__ . '/public/js/confirm.js',
  __DIR__ . '/public/js/login.js',
  __DIR__ . '/public/js/register.js',
  __DIR__ . '/public/js/resetPassword.js',
  __DIR__ . '/public/js/auth/verify2fa.js',
  __DIR__ . '/public/js/auth/noconfirmed.js',

];

foreach ($cssFiles as $file) {
  if (!file_exists($file)) {
    echo "❌ Fichier introuvable : $file\n";
    continue;
  }

  $minFile = str_replace('.css', '.min.css', $file);
  (new Minify\CSS($file))->minify($minFile);
  echo "✅ CSS minifié : $minFile\n";
}

foreach ($jsFiles as $file) {
  if (!file_exists($file)) {
    echo "❌ Fichier introuvable : $file\n";
    continue;
  }

  $minFile = str_replace('.js', '.min.js', $file);
  (new Minify\JS($file))->minify($minFile);
  echo "✅ JS minifié : $minFile\n";
}

echo "\n✅ Build terminé.\n";
