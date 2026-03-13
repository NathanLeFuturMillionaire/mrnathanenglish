<?php

declare(strict_types=1);

/**
 * Helper de formatage de date en français
 */
function formatDateFr(?string $date): string
{
  if (empty($date)) {
    return '';
  }

  $formatter = new IntlDateFormatter(
    'fr_FR',
    IntlDateFormatter::LONG,
    IntlDateFormatter::NONE,
    'Europe/Paris',
    IntlDateFormatter::GREGORIAN,
    "d MMMM yyyy"
  );

  return $formatter->format(new DateTime($date));
}
