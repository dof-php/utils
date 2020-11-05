<?php

$gwt->unit('\DOF\Util\Str::mask()', function ($t) {
    $t->eq(\DOF\Util\Str::mask('13344445555', 4, 7), '133****5555');
    $t->eq(\DOF\Util\Str::mask('13344445555', 4, 7, 'x'), '133xxxx5555');
    $t->eq(\DOF\Util\Str::mask('中华人民共和国', 5, 7, 'x'), '中华人民xxx');
});

$gwt->unit('\DOF\Util\Str::partition()', function ($t) {
    $t->eq(\DOF\Util\Str::partition('user'), 3994143665);
    $t->eq(\DOF\Util\Str::partition('user', 8), 3994143665);
    $t->eq(\DOF\Util\Str::partition('user', 14), 67010611012195044);
});

$gwt->unit('\DOF\Util\Str::wraps()', function ($t) {
    $t->eq(\DOF\Util\Str::wraps(['aaa', 'bbb'], '`', PHP_EOL), "`aaa`\n`bbb`");
    $t->eq(\DOF\Util\Str::wraps(['aaa', 'bbb'], '`'), "`aaa`,`bbb`");
    $t->eq(\DOF\Util\Str::wraps(['aaa', 'bbb'], '`', '<br>'), "`aaa`<br>`bbb`");
    $t->eq(\DOF\Util\Str::wraps(['aaa' => 'xxx', 'bbb' => 'yyy'], '`', "\n"), \join("\n", [
        \DOF\Util\Str::wrap(\DOF\Util\JSON::encode(['aaa' => 'xxx', 'bbb' => 'yyy']), '`'),
    ]));
});

$gwt->unit('\DOF\Util\Str::wrap()', function ($t) {
    $t->eq(\DOF\Util\Str::wrap(1234, '`', '-'), '`1234`');
    $t->eq(\DOF\Util\Str::wrap([], '`', '-'), '-');
    $t->eq(\DOF\Util\Str::wrap('  ', '`', '-'), '-');
    $t->eq(\DOF\Util\Str::wrap(0, '`', '-'), '`0`');
    $t->eq(\DOF\Util\Str::wrap(null, '`', '-'), '-');
});

$gwt->false('Test two strings are equal: cs', function ($tester) {
    return \DOF\Util\Str::eq('sss', 'SSS', false);
});
$gwt->true('Test two strings are equal: ci', function ($tester) {
    return \DOF\Util\Str::eq('sss', 'SSS', true);
});
$gwt->true('Test two strings are equal: ci+trim #1', function ($tester) {
    return \DOF\Util\Str::eq(' sss', 'SSS   ', true, true);
});
$gwt->false('Test two strings are equal: ci+trim #2', function ($tester) {
    return \DOF\Util\Str::eq(' sss', 'SSS   ', true, false);
});

$gwt->eq('Test \DOF\Util\Str::arr(): #1', function ($tester) {
    return \DOF\Util\Str::arr('1,,,,,2,3 ,,,,,,');
}, ['1', '2', '3']);

$gwt->eq('Test \DOF\Util\Str::arr(): #2', function ($tester) {
    return \DOF\Util\Str::arr('a. b.c', '.');
}, ['a', 'b', 'c']);

$gwt->unit('Test \DOF\Util\Str::literal()', function ($t) {
    $t->eq(\DOF\Util\Str::literal(null), 'null');
    $t->eq(\DOF\Util\Str::literal(true), 'true');
    $t->eq(\DOF\Util\Str::literal(false), 'false');
    $t->eq(\DOF\Util\Str::literal(1.234), '1.234');
    $t->eq(\DOF\Util\Str::literal(new class {
    }), 'object');
    $t->eq(\DOF\Util\Str::literal([]), 'array');
});

$gwt->unit('Test \DOF\Util\Str::charcase()', function ($t) {
    $t->true(\DOF\Util\Str::charcase('a', 0));
    $t->false(\DOF\Util\Str::charcase('0', 0));
    $t->false(\DOF\Util\Str::charcase('0', 1));
    $t->false(\DOF\Util\Str::charcase(' ', 0));
    $t->false(\DOF\Util\Str::charcase(' ', 1));
    $t->false(\DOF\Util\Str::charcase('$', 0));
    $t->false(\DOF\Util\Str::charcase('$', 1));
    $t->false(\DOF\Util\Str::charcase('中国', 0));
    $t->false(\DOF\Util\Str::charcase('中国', 1));
    $t->false(\DOF\Util\Str::charcase('🇨🇳', 0));
    $t->false(\DOF\Util\Str::charcase('🇨🇳', 1));
});

$gwt->unit('Test \DOF\Util\Str::start()', function ($t) {
    $t->true(\DOF\Util\Str::start('a', 'abcdefg'));
    $t->true(\DOF\Util\Str::start('abc', 'abcdefg'));
    $t->false(\DOF\Util\Str::start('', 'abcdefg'));
    $t->false(\DOF\Util\Str::start(' abc', 'abcdefg'));
});

$gwt->unit('Test \DOF\Util\Str::end()', function ($t) {
    $t->true(\DOF\Util\Str::end('g', 'abcdefg'));
    $t->true(\DOF\Util\Str::end('efg', 'abcdefg'));
    $t->false(\DOF\Util\Str::end('', 'abcdefg'));
    $t->false(\DOF\Util\Str::end(' g', 'abcdefg'));
});

$gwt->unit('Test \DOF\Util\Str::shift()', function ($t) {
    $t->eq(\DOF\Util\Str::shift('abcdefg', 1), 'bcdefg');
    $t->eq(\DOF\Util\Str::shift('abcdefg', 'abc'), 'defg');
    $t->eq(\DOF\Util\Str::shift('abcdefg', 1, true), 'abcdef');
    $t->eq(\DOF\Util\Str::shift('abcdefg', 'efg', true), 'abcd');
});

$gwt->unit('Test \DOF\Util\Str::middle()', function ($t) {
    $t->eq(\DOF\Util\Str::middle('abcdefg', 'a', 'g'), 'bcdef');
    $t->eq(\DOF\Util\Str::middle('abcdefg', 1, 1), 'bcdef');
    $t->eq(\DOF\Util\Str::middle('abcdefg', 'ab', 'fg'), 'cde');
});

$gwt->unit('Test \DOF\Util\Str::fixed()', function ($t) {
    $t->eq(\DOF\Util\Str::fixed('aaaabbbbccccdddd', 100, ' ... '), 'aaaabbbbccccdddd');
    $t->eq(\DOF\Util\Str::fixed('aaaabbbbccccdddd', 13, ' ... '), 'aaaa ... dddd');
});

$gwt->unit('Test \DOF\Util\Str::first()', function ($t) {
    $t->eq(\DOF\Util\Str::first('abcdefghijklmnopqrstuvwzxy', 4), 'abcd');
    $t->eq(\DOF\Util\Str::first('爱我中华11110000xxxx', 6), '爱我中华11');
    $t->eq(\DOF\Util\Str::first('a', 4), 'a');
});

$gwt->unit('Test \DOF\Util\Str::last()', function ($t) {
    $t->eq(\DOF\Util\Str::last('abcdefghijklmnopqrstuvwzxy', 4), 'wzxy');
    $t->eq(\DOF\Util\Str::last('爱我中华11110000xxxx河山大好', 6), 'xx河山大好');
    $t->eq(\DOF\Util\Str::last('ab', 4), 'ab');
    $t->eq(\DOF\Util\Str::last('abcdefg', 4, false), 'efg');
    $t->eq(\DOF\Util\Str::last('爱我中华xxxx河山大好', 8, false), '河山大好');
});
