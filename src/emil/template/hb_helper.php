<?php

// dbg('loading helper', $opts);

return [
    'pluralize' => function ($total, $singular, $plural) {
        return $total == 1 ? $singular : $plural;
    },
    'pluralize0' => function ($total, $zero, $singular, $plural) {
        return (!$total) ? $zero : ($total == 1 ? $singular : $plural);
    },
    'markdown' => function ($md) use ($opts) {
        // can't use binded opts here
        // at eval time
        // maybe double add handler on eval time
        // TODO: some tests
        // return $opts['markdown']->text($md);
        $pd = new \Parsedown();
        return $pd->text($md);
    }
];
