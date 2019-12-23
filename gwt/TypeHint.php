<?php

$gwt->unit('Test \DOF\Util\TypeHint::typehint()', function ($t) {
    $t->exceptor(function () {
        return \DOF\Util\TypeHint::typehint('xxxx');
    });

    $t->true(\DOF\Util\TypeHint::typehint('int', 1));
});

$gwt->unit('Test \DOF\Util\TypeHint::bool()', function ($t) {
    $t->true(\DOF\Util\TypeHint::bool('0'));
    $t->true(\DOF\Util\TypeHint::bool(0));
    $t->true(\DOF\Util\TypeHint::bool('1'));
    $t->true(\DOF\Util\TypeHint::bool(1));
    $t->true(\DOF\Util\TypeHint::bool(true));
    $t->true(\DOF\Util\TypeHint::bool(false));
    $t->false(\DOF\Util\TypeHint::bool(null));
    $t->false(\DOF\Util\TypeHint::bool(2));
    $t->false(\DOF\Util\TypeHint::bool('2'));
    $t->false(\DOF\Util\TypeHint::bool(function () {
    }));
    $t->false(\DOF\Util\TypeHint::bool(new class {
    }));
});
$gwt->unit('Test \DOF\Util\TypeHint::int()', function ($t) {
    $t->true(\DOF\Util\TypeHint::int(1));
    $t->true(\DOF\Util\TypeHint::int('1'));
    $t->true(\DOF\Util\TypeHint::int(0));
    $t->true(\DOF\Util\TypeHint::int('0'));
    $t->true(\DOF\Util\TypeHint::int(1.0));
    $t->false(\DOF\Util\TypeHint::int(0.1));
    $t->false(\DOF\Util\TypeHint::int(null));
    $t->false(\DOF\Util\TypeHint::int('abcd'));
    $t->false(\DOF\Util\TypeHint::int(function () {
    }));
    $t->false(\DOF\Util\TypeHint::int(new class {
    }));
});
$gwt->unit('Test \DOF\Util\TypeHint::string()', function ($t) {
    $t->true(\DOF\Util\TypeHint::string(1));
    $t->true(\DOF\Util\TypeHint::string(1.0));
    $t->true(\DOF\Util\TypeHint::string('China 中国'));
    $t->false(\DOF\Util\TypeHint::string(function () {
    }));
    $t->false(\DOF\Util\TypeHint::string(false));
    $t->false(\DOF\Util\TypeHint::string(null));
    $t->true(\DOF\Util\TypeHint::string(new class {
        public function __toString()
        {
            return 'string';
        }
    }));
    $t->false(\DOF\Util\TypeHint::string(new class {
        public function __toString()
        {
            return 1;
        }
    }));
    $t->false(\DOF\Util\TypeHint::string(new class {
    }));
});
$gwt->unit('Test \DOF\Util\TypeHint::float()', function ($t) {
    $t->true(\DOF\Util\TypeHint::float(0));
    $t->true(\DOF\Util\TypeHint::float(1));
    $t->false(\DOF\Util\TypeHint::float(null));
    $t->true(\DOF\Util\TypeHint::float(1.0));
    $t->false(\DOF\Util\TypeHint::float('string'));
});
$gwt->unit('Test \DOF\Util\TypeHint::array()', function ($t) {
    $t->true(\DOF\Util\TypeHint::array([]));
    $t->false(\DOF\Util\TypeHint::array(null));
    $t->false(\DOF\Util\TypeHint::array(0));
    $t->false(\DOF\Util\TypeHint::array(1));
    $t->true(\DOF\Util\TypeHint::array(new class {
        public function __toArray()
        {
            return [];
        }
    }));
    $t->false(\DOF\Util\TypeHint::array(new class {
        public function __toArray()
        {
            return null;
        }
    }));
});
$gwt->unit('Test \DOF\Util\TypeHint::bint()', function ($t) {
    $t->true(\DOF\Util\TypeHint::bint(0));
    $t->true(\DOF\Util\TypeHint::bint(1));
    $t->true(\DOF\Util\TypeHint::bint('0'));
    $t->true(\DOF\Util\TypeHint::bint('1'));
    $t->false(\DOF\Util\TypeHint::bint('2'));
    $t->false(\DOF\Util\TypeHint::bint(-1));
    $t->false(\DOF\Util\TypeHint::bint(''));
    $t->false(\DOF\Util\TypeHint::bint(null));
});
$gwt->unit('Test \DOF\Util\TypeHint::uint()', function ($t) {
    $t->true(\DOF\Util\TypeHint::uint('0'));
    $t->true(\DOF\Util\TypeHint::uint('1'));
    $t->true(\DOF\Util\TypeHint::uint(0));
    $t->false(\DOF\Util\TypeHint::uint(''));
    $t->false(\DOF\Util\TypeHint::uint(-1));
    $t->false(\DOF\Util\TypeHint::uint(null));
});
$gwt->unit('Test \DOF\Util\TypeHint::pint()', function ($t) {
    $t->true(\DOF\Util\TypeHint::pint(1));
    $t->true(\DOF\Util\TypeHint::pint('1'));
    $t->false(\DOF\Util\TypeHint::pint(0));
    $t->false(\DOF\Util\TypeHint::pint('0'));
    $t->false(\DOF\Util\TypeHint::pint(''));
    $t->false(\DOF\Util\TypeHint::pint(null));
});
$gwt->unit('Test TypeCast::nint()', function ($t) {
    $t->true(\DOF\Util\TypeHint::nint(-1));
    $t->true(\DOF\Util\TypeHint::nint(-1.0));
    $t->true(\DOF\Util\TypeHint::nint('-1'));
    $t->true(\DOF\Util\TypeHint::nint('-1.0'));

    $t->false(\DOF\Util\TypeHint::nint(0));
    $t->false(\DOF\Util\TypeHint::nint('0'));
    $t->false(\DOF\Util\TypeHint::nint(1));
    $t->false(\DOF\Util\TypeHint::nint('1'));
    $t->false(\DOF\Util\TypeHint::nint(''));
    $t->false(\DOF\Util\TypeHint::nint(null));
});
$gwt->unit('Test \DOF\Util\TypeHint::list()', function ($t) {
    $t->false(\DOF\Util\TypeHint::list([]));
    $t->false(\DOF\Util\TypeHint::list(0));
    $t->false(\DOF\Util\TypeHint::list(''));
    $t->false(\DOF\Util\TypeHint::list('0'));
    $t->false(\DOF\Util\TypeHint::list(null));
    $t->true(\DOF\Util\TypeHint::list([1, 2, 3]));
    $t->false(\DOF\Util\TypeHint::list(['a' => 1, 2, 3]));
});
$gwt->unit('Test \DOF\Util\TypeHint::bigint()', function ($t) {
    $t->false(\DOF\Util\TypeHint::bigint([]));
    $t->true(\DOF\Util\TypeHint::bigint(0, false));
    $t->true(\DOF\Util\TypeHint::bigint(0, true));
    $t->true(\DOF\Util\TypeHint::bigint(-1, false));
    $t->false(\DOF\Util\TypeHint::bigint(-1, true));
    $t->false(\DOF\Util\TypeHint::bigint(''));
    $t->true(\DOF\Util\TypeHint::bigint('0'));
    $t->false(\DOF\Util\TypeHint::bigint(null));
    $t->true(\DOF\Util\TypeHint::bigint(\DOF\Util\Num::big(pow(2, 63)) - 1, true));
    $t->false(\DOF\Util\TypeHint::bigint(\DOF\Util\Num::big(pow(2, 64)), true));
    $t->true(\DOF\Util\TypeHint::bigint(-\DOF\Util\Num::big(pow(2, 63)), false));
    $t->true(\DOF\Util\TypeHint::bigint(\DOF\Util\Num::big(pow(2, 63)) - 1, false));
    $t->false(\DOF\Util\TypeHint::bigint(\DOF\Util\Num::big(pow(2, 63)), false));
});
$gwt->unit('Test \DOF\Util\TypeHint::mediumint()', function ($t) {
    $t->false(\DOF\Util\TypeHint::mediumint([]));
    $t->true(\DOF\Util\TypeHint::mediumint(0, false));
    $t->true(\DOF\Util\TypeHint::mediumint(0, true));
    $t->true(\DOF\Util\TypeHint::mediumint(-1, false));
    $t->false(\DOF\Util\TypeHint::mediumint(-1, true));
    $t->false(\DOF\Util\TypeHint::mediumint(''));
    $t->true(\DOF\Util\TypeHint::mediumint('0'));
    $t->false(\DOF\Util\TypeHint::mediumint(null));
    $t->true(\DOF\Util\TypeHint::mediumint(16777215, true));
    $t->false(\DOF\Util\TypeHint::mediumint(16777216, true));
    $t->true(\DOF\Util\TypeHint::mediumint(-8388608, false));
    $t->true(\DOF\Util\TypeHint::mediumint(8388607, false));
    $t->false(\DOF\Util\TypeHint::mediumint(8388608, false));
});
$gwt->unit('Test \DOF\Util\TypeHint::smallint()', function ($t) {
    $t->false(\DOF\Util\TypeHint::smallint([]));
    $t->true(\DOF\Util\TypeHint::smallint(0, false));
    $t->true(\DOF\Util\TypeHint::smallint(0, true));
    $t->true(\DOF\Util\TypeHint::smallint(-1, false));
    $t->false(\DOF\Util\TypeHint::smallint(-1, true));
    $t->false(\DOF\Util\TypeHint::smallint(''));
    $t->true(\DOF\Util\TypeHint::smallint('0'));
    $t->false(\DOF\Util\TypeHint::smallint(null));
    $t->true(\DOF\Util\TypeHint::smallint(65535, true));
    $t->false(\DOF\Util\TypeHint::smallint(65536, true));
    $t->true(\DOF\Util\TypeHint::smallint(-32768, false));
    $t->true(\DOF\Util\TypeHint::smallint(32767, false));
    $t->false(\DOF\Util\TypeHint::smallint(32768, false));
});
$gwt->unit('Test \DOF\Util\TypeHint::tinyint()', function ($t) {
    $t->false(\DOF\Util\TypeHint::tinyint([]));
    $t->true(\DOF\Util\TypeHint::tinyint(0, false));
    $t->true(\DOF\Util\TypeHint::tinyint(0, true));
    $t->true(\DOF\Util\TypeHint::tinyint(-1, false));
    $t->false(\DOF\Util\TypeHint::tinyint(-1, true));
    $t->false(\DOF\Util\TypeHint::tinyint(''));
    $t->true(\DOF\Util\TypeHint::tinyint('0'));
    $t->false(\DOF\Util\TypeHint::tinyint(null));
    $t->true(\DOF\Util\TypeHint::tinyint(255, true));
    $t->false(\DOF\Util\TypeHint::tinyint(256, true));
    $t->true(\DOF\Util\TypeHint::tinyint(-128, false));
    $t->true(\DOF\Util\TypeHint::tinyint(127, false));
    $t->false(\DOF\Util\TypeHint::tinyint(128, false));
});
