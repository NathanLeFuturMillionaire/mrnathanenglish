<?php

namespace App\controllers;

class Controller
{
  protected function redirect(string $path): void
  {
    header("Location: " . $path);
    exit;
  }
}
