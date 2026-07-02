<?php

class ErrorPage
{
  public static function render(int $code, string $heading, array $lines = [], array $actions = []): void
  {
    http_response_code($code);

    $title   = $heading;
    $actions = $actions ?: [['label' => '← Go Back Home', 'href' => ROOT . '/home']];

    require VIEWSPATH . '/error.view.php';
    exit;
  }
}
