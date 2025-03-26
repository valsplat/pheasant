<?php

require_once __DIR__ . '/common.php';

$num = isset($argv[1]) ? $argv[1] : 10000;

$binder = new Pheasant\Database\Binder();
$binds = [
    ['SELECT * FROM table WHERE column=?', ['test']],
    ['x=?', ['10\'; DROP TABLE --']],
    ['column1=? and column2=?', [false, true]],
    ["name='???' and llamas=?", [24]],
    ["name='\'7r' and llamas=?", [24]],
    ["name='\'7r\\\\' and another='test question?' and llamas=?", [24]],
    ["name='\'7r\\\\' and x='\'7r' and llamas=?", [24]],
];

printf("binding %d statements %d times\n", count($binds), $num);

benchmark($num, function () use ($binds, $binder) {
    foreach ($binds as $bind) {
        $binder->bind($bind[0], $bind[1]);
    }
});
