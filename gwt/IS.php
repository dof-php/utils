<?php

$gwt->unit('Test a \DOF\Util\IS::host()', function ($t) {
    $t->false(\DOF\Util\IS::host(null));
    $t->false(\DOF\Util\IS::host([]));
    $t->true(\DOF\Util\IS::host(0));
    $t->true(\DOF\Util\IS::host(110));
    $t->true(\DOF\Util\IS::host(00110));
    $t->true(\DOF\Util\IS::host('0'));
    $t->true(\DOF\Util\IS::host('xxx.com'));
    $t->true(\DOF\Util\IS::host('localhost'));
    $t->true(\DOF\Util\IS::host('0.0.0.0'));
});

$gwt->unit('Test a \DOF\Util\IS::email()', function ($t) {
    $t->true(\DOF\Util\IS::email('abcd@xxx.com'));
    $t->true(\DOF\Util\IS::email('ab.c-d@xxx.com'));
    $t->false(\DOF\Util\IS::email('abcd#xxx'));
    $t->false(\DOF\Util\IS::email('0@0.0'));
    $t->false(\DOF\Util\IS::email(null));
    $t->false(\DOF\Util\IS::email([]));
    $t->false(\DOF\Util\IS::email(0));
});

$gwt->unit('Test a \DOF\Util\IS::length()', function ($t) {
    $t->true(\DOF\Util\IS::length('abcd', '4'));
    $t->true(\DOF\Util\IS::length('abcd', 4));
    $t->true(\DOF\Util\IS::length('中华民族', 4));
    $t->true(\DOF\Util\IS::length(4, 1));
    $t->true(\DOF\Util\IS::length([], 0));
    $t->false(\DOF\Util\IS::length(null, 0));
});

$gwt->unit('Test a \DOF\Util\IS::type()', function ($t) {
    $t->false(\DOF\Util\IS::type('abcdefg', 'abcdefg'));
    $t->true(\DOF\Util\IS::type('0', 'int'));
    $t->true(\DOF\Util\IS::type('int', 'support'));
});

$gwt->unit('Test a \DOF\Util\IS::confirm()', function ($t) {
    $t->true(\DOF\Util\IS::confirm('yes'));
    $t->true(\DOF\Util\IS::confirm('y'));
    $t->true(\DOF\Util\IS::confirm('yEs'));
    $t->true(\DOF\Util\IS::confirm('y'));
    $t->true(\DOF\Util\IS::confirm('Y'));
    $t->true(\DOF\Util\IS::confirm('1'));
    $t->true(\DOF\Util\IS::confirm('true'));
    $t->true(\DOF\Util\IS::confirm(true));
    $t->false(\DOF\Util\IS::confirm(null));
    $t->false(\DOF\Util\IS::confirm(false));
    $t->false(\DOF\Util\IS::confirm(0));
    $t->false(\DOF\Util\IS::confirm('N'));
    $t->false(\DOF\Util\IS::confirm('n'));
    $t->false(\DOF\Util\IS::confirm('no'));
    $t->false(\DOF\Util\IS::confirm('nO'));
    $t->false(\DOF\Util\IS::confirm('Y', []));
});

$gwt->unit('Test a \DOF\Util\IS::ciins()', function ($t) {
    $t->false(\DOF\Util\IS::ciins([null, 0], [null, 0]));
    $t->true(\DOF\Util\IS::ciins(['A', 0], ['a', 'b', '0']));
});

$gwt->unit('Test a \DOF\Util\IS::ciin()', function ($t) {
    $t->false(\DOF\Util\IS::ciin(null, [1, 2, 3]));
    $t->true(\DOF\Util\IS::ciin(1, [1, 2, 3]));
    $t->true(\DOF\Util\IS::ciin(1, [1, '2', 3]));
    $t->true(\DOF\Util\IS::ciin('1', [1, '2', 3]));
    $t->true(\DOF\Util\IS::ciin('0', [0, 1, '2', 3]));
    $t->true(\DOF\Util\IS::ciin('0', [0, 1, '2', 3, null]));
    $t->true(\DOF\Util\IS::ciin('a', ['A', 0, 1, '2', 3, null]));
});

$gwt->unit('Test \DOF\Util\IS::array()', function ($t) {
    $t->true(\DOF\Util\IS::array([]));
    $t->false(\DOF\Util\IS::array(null));
    $t->false(\DOF\Util\IS::array(0));
    $t->false(\DOF\Util\IS::array(''));
    $t->false(\DOF\Util\IS::array(' '));
    $t->false(\DOF\Util\IS::array('0'));
    $t->true(\DOF\Util\IS::array([1, 2, 3], 'index'));
    $t->false(\DOF\Util\IS::array(['a' => 1, 2, 3], 'index'));
    $t->false(\DOF\Util\IS::array(['a' => 1, 2, 3], 'assoc'));
    $t->true(\DOF\Util\IS::array(['a' => 1, 2, 3]));
});

$gwt->unit('Test \DOF\Util\IS::exceptor()', function ($t) {
    $t->false(\DOF\Util\IS::exceptor(new class {
    }));
    $t->false(\DOF\Util\IS::exceptor(new \Exception));
    $t->true(\DOF\Util\IS::exceptor(new \DOF\Util\Exceptor('Test')));
    $t->false(\DOF\Util\IS::exceptor(\DOF\Util\Exceptor::class));
});

$gwt->unit('Test \DOF\Util\IS::empty()', function ($t) {
    $t->false(\DOF\Util\IS::empty(0));
    $t->false(\DOF\Util\IS::empty(0.0));
    $t->false(\DOF\Util\IS::empty('0'));
    $t->false(\DOF\Util\IS::empty(false));
    $t->true(\DOF\Util\IS::empty(null));
    $t->true(\DOF\Util\IS::empty(''));
    $t->true(\DOF\Util\IS::empty('    '));
    $t->true(\DOF\Util\IS::empty([]));
});
