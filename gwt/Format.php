<?php

$gwt->unit('Test \DOF\Util\Format::path()', function ($t) {
    $t->eq(\DOF\Util\Format::path('///a/b/c///', '/'), '/a/b/c');
    $t->eq(\DOF\Util\Format::path('a/b/c///', '/'), 'a/b/c');
    $t->eq(\DOF\Util\Format::path('   /a/b/c///', '/'), '/a/b/c');
    $t->eq(\DOF\Util\Format::path('///a/b/c///', '-'), '///a/b/c///');
});

$gwt->unit('Test \DOF\Util\Format::classname()', function ($t) {
    $t->null(\DOF\Util\Format::classname(''));
    $t->null(\DOF\Util\Format::classname([]));
    $t->eq(\DOF\Util\Format::classname(\DOF\Util\Format::class), 'Format');
    $t->eq(\DOF\Util\Format::classname(\DOF\Util\Format::class, true), 'DOF\Util\Format');
    $t->eq(\DOF\Util\Format::classname(new \DOF\Util\Format), 'Format');
});

$gwt->unit('Test \DOF\Util\Format::namespace()', function ($t) {
    $t->eq(\DOF\Util\Format::namespace('aaaa'), '');
    $t->eq(\DOF\Util\Format::namespace('aaaa', '/', true), 'Aaaa');
    $t->eq(\DOF\Util\Format::namespace('aaaa', '/', true, true), '\\Aaaa');
    $t->eq(\DOF\Util\Format::namespace('aaaa', '/', false, true), '');
    $t->eq(\DOF\Util\Format::namespace(''), '');
    $t->eq(\DOF\Util\Format::namespace('\\', '/', false, true), '');
    $t->eq(\DOF\Util\Format::namespace('\\', '/', true, true), '\\');
    $t->eq(\DOF\Util\Format::namespace('\\', '\\', true, true), '\\');
    $t->eq(\DOF\Util\Format::namespace('A\C\D'), 'A\C');
    $t->eq(\DOF\Util\Format::namespace('AbcDef\GhiJkl'), 'AbcDef');
    $t->eq(\DOF\Util\Format::namespace('abc_def\GhiJkl'), 'AbcDef');
    $t->eq(\DOF\Util\Format::namespace('A/C/D', '/'), 'A\C');
    $t->eq(\DOF\Util\Format::namespace('\A\C\D'), 'A\C');
    $t->eq(\DOF\Util\Format::namespace('/A/C/D', '/'), 'A\C');
    $t->eq(\DOF\Util\Format::namespace('\A\C\D', '\\', false, true), '\A\C');
    $t->eq(\DOF\Util\Format::namespace('-A-C-D', '-', false, true), '\A\C');
    $t->eq(\DOF\Util\Format::namespace('\A\C\D', '\\', true, true), '\A\C\D');
    $t->eq(\DOF\Util\Format::namespace('\A\\\C\D'), 'A\C');
    $t->eq(\DOF\Util\Format::namespace('\A\\\C\D', '\\', false, true), '\A\C');
});

$gwt->unit('Test \DOF\Util\Format::route()', function ($t) {
    $t->eq(\DOF\Util\Format::route(''), '/');
    $t->eq(\DOF\Util\Format::route('', '$', false), []);
    $t->eq(\DOF\Util\Format::route('', 'xxxx'), 'xxxx');
    $t->eq(\DOF\Util\Format::route('///a/b-c/'), 'a/b-c');
    $t->eq(\DOF\Util\Format::route('///a/b-c/', '/', false), ['a', 'b-c']);
    $t->eq(\DOF\Util\Format::route('///a/b/c///?k=v'), 'a/b/c/?k=v');
    $t->eq(\DOF\Util\Format::route('///a/b-c/', ','), '///a/b-c/');
});

$gwt->unit('Test \DOF\Util\Format::u2c()', function ($t) {
    $t->eq(\DOF\Util\Format::u2c('a__b_c____', CASE_LOWER), 'aBC');
    $t->eq(\DOF\Util\Format::u2c('a__b_c____', CASE_UPPER), 'ABC');
    $t->eq(\DOF\Util\Format::u2c('a__b_c____'), 'ABC');
    $t->eq(\DOF\Util\Format::u2c('__a__b_c____'), 'ABC');
    $t->eq(\DOF\Util\Format::u2c('__a__b_c____D'), 'ABCD');
    $t->eq(\DOF\Util\Format::u2c('__a__bc____'), 'ABc');
});

$gwt->unit('Test \DOF\Util\Format::c2u()', function ($t) {
    $t->eq(\DOF\Util\Format::c2u('AbC'), 'ab_c');
    $t->eq(\DOF\Util\Format::c2u('ABC'), 'a_b_c');
    $t->eq(\DOF\Util\Format::c2u('AbcDefGHI'), 'abc_def_g_h_i');
});

$gwt->unit('Test \DOF\Util\Format::string()', function ($t) {
    $t->eq(\DOF\Util\Format::string(null), '');
    $t->eq(\DOF\Util\Format::string(42), '42');
    $t->eq(\DOF\Util\Format::string(['foo' => 'bar']), '{"foo":"bar"}');
    $t->exception(function () {
        \DOF\Util\Format::string(new class {
        });
    });
    $t->eq(\DOF\Util\Format::string(new class {
        public function __toString()
        {
            return '__toString';
        }
    }), '__toString');
    $t->eq(\DOF\Util\Format::string(new class {
        public function __toArray()
        {
            return ['foo' => 'bar'];
        }
    }), '{"foo":"bar"}');
    $t->eq(\DOF\Util\Format::string(new class {
        public function __toArray()
        {
            return '__toArray';
        }
    }), '__toArray');
});

$gwt->eq('Test microtime formatting: #1', function ($tester) {
    $raw = 1569902445.8828;
    $tz = \date_default_timezone_get();

    \date_default_timezone_set('UTC');
    $res = \DOF\Util\Format::microtime('Y-m-d H:i:s', '.', $raw);
    \date_default_timezone_set($tz);

    return $res;
}, '2019-10-01 04:00:45.8828');

$gwt->eq('Test microtime formatting: #2', function ($tester) {
    $raw = 1569902445.8828;
    $tz = \date_default_timezone_get();

    \date_default_timezone_set('UTC');
    $res = \DOF\Util\Format::microtime('T Ymd His', ' ', $raw);
    \date_default_timezone_set($tz);

    return $res;
}, 'UTC 20191001 040045 8828');
