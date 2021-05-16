<?php

return [
    'pluralize' => function ($total, $singular, $plural) {
        return $total == 1 ? $singular : $plural;
    },
    'pluralize0' => function ($total, $zero, $singular, $plural) {
        return (!$total) ? $zero : ($total == 1 ? $singular : $plural);
    },
    'markdown' => function ($md) use ($opts) {
        return $opts['markdown']->text($md);
    }
];
