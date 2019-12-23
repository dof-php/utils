<?php

$gwt->unit('Test \DOF\Util\TypeCast::bool()', function ($t) {
    $t->false(\DOF\Util\TypeCast::bool('0'));
    $t->false(\DOF\Util\TypeCast::bool(0));
    $t->true(\DOF\Util\TypeCast::bool('1'));
    $t->true(\DOF\Util\TypeCast::bool(1));
    $t->true(\DOF\Util\TypeCast::bool(true));
    $t->false(\DOF\Util\TypeCast::bool(false));
    $t->false(\DOF\Util\TypeCast::bool(null, true));
    $t->true(\DOF\Util\TypeCast::bool(2, true));
    $t->true(\DOF\Util\TypeCast::bool('2', true));
    $t->exceptor(function () {
        \DOF\Util\TypeCast::bool(null);
    }, \DOF\Util\Exceptor\TypeCastExceptor::class);
    $t->exceptor(function () {
        \DOF\Util\TypeCast::bool(2);
    }, \DOF\Util\Exceptor\TypeCastExceptor::class);
    $t->exceptor(function () {
        \DOF\Util\TypeCast::bool('2');
    }, \DOF\Util\Exceptor\TypeCastExceptor::class);
    $t->exceptor(function () {
        \DOF\Util\TypeCast::bool(function () {
        });
    }, \DOF\Util\Exceptor\TypeCastExceptor::class);
    $t->exceptor(function () {
        \DOF\Util\TypeCast::bool(new class {
        });
    }, \DOF\Util\Exceptor\TypeCastExceptor::class);
});

$gwt->unit('Test \DOF\Util\TypeCast::int()', function ($t) {
    $t->eq(\DOF\Util\TypeCast::int(1), 1);
    $t->eq(\DOF\Util\TypeCast::int('1'), 1);
    $t->eq(\DOF\Util\TypeCast::int(0), 0);
    $t->eq(\DOF\Util\TypeCast::int('0'), 0);
    $t->eq(\DOF\Util\TypeCast::int(1.0), 1);
    $t->eq(\DOF\Util\TypeCast::int(0.1, true), 0);
    $t->eq(\DOF\Util\TypeCast::int(null, true), 0);
    $t->eq(\DOF\Util\TypeCast::int('abcd', true), 0);
    $t->exceptor(function () {
        \DOF\Util\TypeCast::int(0.1);
    }, \DOF\Util\Exceptor\TypeCastExceptor::class);
    $t->exceptor(function () {
        \DOF\Util\TypeCast::int(null);
    }, \DOF\Util\Exceptor\TypeCastExceptor::class);
    $t->exceptor(function () {
        \DOF\Util\TypeCast::int('abcd');
    }, \DOF\Util\Exceptor\TypeCastExceptor::class);
    $t->exceptor(function () {
        \DOF\Util\TypeCast::int(function () {
        });
    }, \DOF\Util\Exceptor\TypeCastExceptor::class);
    $t->exceptor(function () {
        \DOF\Util\TypeCast::int(new class {
        });
    }, \DOF\Util\Exceptor\TypeCastExceptor::class);
});

$gwt->unit('Test \DOF\Util\TypeCast::string()', function ($t) {
    $t->eq(\DOF\Util\TypeCast::string(1), '1');
    $t->eq(\DOF\Util\TypeCast::string(1.000), '1');
    $t->eq(\DOF\Util\TypeCast::string(1.001), '1.001');
    $t->eq(\DOF\Util\TypeCast::string(new class {
        public function __toString()
        {
            return 'string';
        }
    }), 'string');
    $t->eq(\DOF\Util\TypeCast::string('China 中国'), 'China 中国');
    $t->exceptor(function () {
        \DOF\Util\TypeCast::string(null);
    }, \DOF\Util\Exceptor\TypeCastExceptor::class);
    $t->exceptor(function () {
        \DOF\Util\TypeCast::string(false);
    }, \DOF\Util\Exceptor\TypeCastExceptor::class);
    $t->exceptor(function () {
        \DOF\Util\TypeCast::string(function () {
        });
    }, \DOF\Util\Exceptor\TypeCastExceptor::class);
    $t->exceptor(function () {
        \DOF\Util\TypeCast::string(new class {
            public function __toString()
            {
                return 1;
            }
        });
    }, \DOF\Util\Exceptor\TypeCastExceptor::class);
    $t->exceptor(function () {
        \DOF\Util\TypeCast::string(new class {
        });
    }, \DOF\Util\Exceptor\TypeCastExceptor::class);
});

$gwt->unit('Test \DOF\Util\TypeCast::float()', function ($t) {
    $t->eq(\DOF\Util\TypeCast::float(0), 0.0);
    $t->eq(\DOF\Util\TypeCast::float(1), 1.0);
    $t->exceptor(function () {
        \DOF\Util\TypeCast::float(null);
    }, \DOF\Util\Exceptor\TypeCastExceptor::class);
    $t->eq(\DOF\Util\TypeCast::float(null, true), 0.0);
    $t->eq(\DOF\Util\TypeCast::float(1.0), 1.0);
    $t->eq(\DOF\Util\TypeCast::float(true, true), 1.0);
    $t->exceptor(function () {
        \DOF\Util\TypeCast::float(true);
    }, \DOF\Util\Exceptor\TypeCastExceptor::class);
    $t->exceptor(function () {
        \DOF\Util\TypeCast::float('string');
    }, \DOF\Util\Exceptor\TypeCastExceptor::class);
    $t->eq(\DOF\Util\TypeCast::float('string', true), 0.0);
});

$gwt->unit('Test \DOF\Util\TypeCast::array()', function ($t) {
    $t->eq(\DOF\Util\TypeCast::array([]), []);
    $t->eq(\DOF\Util\TypeCast::array(null, true), []);
    $t->exceptor(function () {
        \DOF\Util\TypeCast::array(null);
    }, \DOF\Util\Exceptor\TypeCastExceptor::class);
    $t->exceptor(function () {
        \DOF\Util\TypeCast::array(0);
    }, \DOF\Util\Exceptor\TypeCastExceptor::class);
    $t->exceptor(function () {
        \DOF\Util\TypeCast::array(1);
    }, \DOF\Util\Exceptor\TypeCastExceptor::class);
    $t->eq(\DOF\Util\TypeCast::array(new class {
        public function __toArray()
        {
            return [];
        }
    }), []);
    $t->exceptor(function () {
        \DOF\Util\TypeCast::array(new class {
            public function __toArray()
            {
                return null;
            }
        });
    }, \DOF\Util\Exceptor\TypeCastExceptor::class);
});

$gwt->unit('Test \DOF\Util\TypeCast::bint()', function ($t) {
    $t->eq(\DOF\Util\TypeCast::bint(0), 0);
    $t->eq(\DOF\Util\TypeCast::bint(1), 1);
    $t->eq(\DOF\Util\TypeCast::bint('0'), 0);
    $t->eq(\DOF\Util\TypeCast::bint('1'), 1);
    $t->exceptor(function () {
        \DOF\Util\TypeCast::bint('2');
    }, \DOF\Util\Exceptor\TypeCastExceptor::class);
    $t->exceptor(function () {
        \DOF\Util\TypeCast::bint(-1);
    }, \DOF\Util\Exceptor\TypeCastExceptor::class);
    $t->exceptor(function () {
        \DOF\Util\TypeCast::bint('');
    }, \DOF\Util\Exceptor\TypeCastExceptor::class);
    $t->eq(\DOF\Util\TypeCast::bint('', true), 0);
    $t->eq(\DOF\Util\TypeCast::bint(null, true), 0);
    $t->exceptor(function () {
        \DOF\Util\TypeCast::bint(null);
    }, \DOF\Util\Exceptor\TypeCastExceptor::class);
});

$gwt->unit('Test \DOF\Util\TypeCast::uint()', function ($t) {
    $t->eq(\DOF\Util\TypeCast::uint('0'), 0);
    $t->eq(\DOF\Util\TypeCast::uint('1'), 1);
    $t->eq(\DOF\Util\TypeCast::uint(0), 0);
    $t->eq(\DOF\Util\TypeCast::uint('', true), 0);
    $t->exceptor(function () {
        \DOF\Util\TypeCast::uint('');
    }, \DOF\Util\Exceptor\TypeCastExceptor::class);
    $t->exceptor(function () {
        \DOF\Util\TypeCast::uint(-1);
    }, \DOF\Util\Exceptor\TypeCastExceptor::class);
    $t->eq(\DOF\Util\TypeCast::uint(null, true), 0);
});

$gwt->unit('Test \DOF\Util\TypeCast::pint()', function ($t) {
    $t->eq(\DOF\Util\TypeCast::pint(1), 1);
    $t->eq(\DOF\Util\TypeCast::pint('1'), 1);

    $t->exceptor(function () {
        \DOF\Util\TypeCast::pint(-1);
    }, \DOF\Util\Exceptor\TypeCastExceptor::class);
    $t->exceptor(function () {
        \DOF\Util\TypeCast::pint(0);
    }, \DOF\Util\Exceptor\TypeCastExceptor::class);
    $t->exceptor(function () {
        \DOF\Util\TypeCast::pint('0');
    }, \DOF\Util\Exceptor\TypeCastExceptor::class);

    $t->exceptor(function () {
        \DOF\Util\TypeCast::pint('', true);
    }, \DOF\Util\Exceptor\TypeCastExceptor::class);
    $t->exceptor(function () {
        \DOF\Util\TypeCast::pint('');
    }, \DOF\Util\Exceptor\TypeCastExceptor::class);
    $t->exceptor(function () {
        \DOF\Util\TypeCast::pint(null, true);
    }, \DOF\Util\Exceptor\TypeCastExceptor::class);
    $t->exceptor(function () {
        \DOF\Util\TypeCast::pint(null);
    }, \DOF\Util\Exceptor\TypeCastExceptor::class);
});

$gwt->unit('Test \DOF\Util\TypeCast::nint()', function ($t) {
    $t->eq(\DOF\Util\TypeCast::nint(-1), -1);
    $t->eq(\DOF\Util\TypeCast::nint(-1.0), -1);
    $t->eq(\DOF\Util\TypeCast::nint('-1'), -1);
    $t->eq(\DOF\Util\TypeCast::nint('-1.0'), -1);

    $t->exceptor(function () {
        \DOF\Util\TypeCast::nint(0);
    }, \DOF\Util\Exceptor\TypeCastExceptor::class);
    $t->exceptor(function () {
        \DOF\Util\TypeCast::nint(1);
    }, \DOF\Util\Exceptor\TypeCastExceptor::class);
    $t->exceptor(function () {
        \DOF\Util\TypeCast::nint('0');
    }, \DOF\Util\Exceptor\TypeCastExceptor::class);
    $t->exceptor(function () {
        \DOF\Util\TypeCast::nint('1', true);
    }, \DOF\Util\Exceptor\TypeCastExceptor::class);
    $t->exceptor(function () {
        \DOF\Util\TypeCast::nint('');
    }, \DOF\Util\Exceptor\TypeCastExceptor::class);
    $t->exceptor(function () {
        \DOF\Util\TypeCast::nint(null, true);
    }, \DOF\Util\Exceptor\TypeCastExceptor::class);
    $t->exceptor(function () {
        \DOF\Util\TypeCast::nint(null);
    }, \DOF\Util\Exceptor\TypeCastExceptor::class);
});

$gwt->unit('Test \DOF\Util\TypeCast::list()', function ($t) {
    $t->exceptor(function () {
        \DOF\Util\TypeCast::list([]);
    }, \DOF\Util\Exceptor\TypeCastExceptor::class);

    $t->exceptor(function () {
        \DOF\Util\TypeCast::list(0);
    }, \DOF\Util\Exceptor\TypeCastExceptor::class);

    $t->exceptor(function () {
        \DOF\Util\TypeCast::list('');
    }, \DOF\Util\Exceptor\TypeCastExceptor::class);

    $t->exceptor(function () {
        \DOF\Util\TypeCast::list('0');
    }, \DOF\Util\Exceptor\TypeCastExceptor::class);

    $t->exceptor(function () {
        \DOF\Util\TypeCast::list(null);
    }, \DOF\Util\Exceptor\TypeCastExceptor::class);

    $t->eq(\DOF\Util\TypeCast::list([1, 2, 3]), [1, 2 ,3]);
    $t->exceptor(function () {
        \DOF\Util\TypeCast::list(['a' => 1, 2, 3]);
    }, \DOF\Util\Exceptor\TypeCastExceptor::class);
});

$gwt->unit('Test \DOF\Util\TypeCast::bigint()', function ($t) {
    $t->exceptor(function () {
        \DOF\Util\TypeCast::bigint([]);
    }, \DOF\Util\Exceptor\TypeCastExceptor::class);

    $t->eq(\DOF\Util\TypeCast::bigint(0, false), 0);
    $t->eq(\DOF\Util\TypeCast::bigint(0, true), 0);
    $t->eq(\DOF\Util\TypeCast::bigint(-1, false), -1);

    $t->eq(\DOF\Util\TypeCast::bigint('', true), 0);
    $t->exceptor(function () {
        \DOF\Util\TypeCast::bigint('');
    }, \DOF\Util\Exceptor\TypeCastExceptor::class);

    $t->eq(\DOF\Util\TypeCast::bigint('0'), 0);
    $t->eq(\DOF\Util\TypeCast::bigint(null, true), 0);
});

$gwt->unit('Test \DOF\Util\TypeCast::mediumint()', function ($t) {
    $t->exceptor(function () {
        \DOF\Util\TypeCast::mediumint([]);
    }, \DOF\Util\Exceptor\TypeCastExceptor::class);

    $t->eq(\DOF\Util\TypeCast::mediumint(0, false), 0);
    $t->eq(\DOF\Util\TypeCast::mediumint('0'), 0);
    $t->eq(\DOF\Util\TypeCast::mediumint('-1', false), -1);
    $t->eq(\DOF\Util\TypeCast::mediumint(-1, false), -1);

    $t->eq(\DOF\Util\TypeCast::mediumint('', true), 0);
    $t->exceptor(function () {
        \DOF\Util\TypeCast::mediumint('');
    }, \DOF\Util\Exceptor\TypeCastExceptor::class);

    $t->eq(\DOF\Util\TypeCast::mediumint(null, true), 0);
    $t->exceptor(function () {
        \DOF\Util\TypeCast::mediumint(null);
    }, \DOF\Util\Exceptor\TypeCastExceptor::class);

    $t->eq(\DOF\Util\TypeCast::mediumint(16777215), 16777215);
    $t->exceptor(function () {
        \DOF\Util\TypeCast::mediumint(16777216);
    }, \DOF\Util\Exceptor\TypeCastExceptor::class);
    $t->exceptor(function () {
        \DOF\Util\TypeCast::smallint(-8388609);
    }, \DOF\Util\Exceptor\TypeCastExceptor::class);
    $t->eq(\DOF\Util\TypeCast::mediumint(-8388608), -8388608);
});

$gwt->unit('Test \DOF\Util\TypeCast::smallint()', function ($t) {
    $t->exceptor(function () {
        \DOF\Util\TypeCast::smallint([]);
    }, \DOF\Util\Exceptor\TypeCastExceptor::class);

    $t->eq(\DOF\Util\TypeCast::smallint(0, false), 0);
    $t->eq(\DOF\Util\TypeCast::smallint('0'), 0);
    $t->eq(\DOF\Util\TypeCast::smallint('-1', false), -1);
    $t->eq(\DOF\Util\TypeCast::smallint(-1, false), -1);

    $t->eq(\DOF\Util\TypeCast::smallint('', true), 0);
    $t->exceptor(function () {
        \DOF\Util\TypeCast::smallint('');
    }, \DOF\Util\Exceptor\TypeCastExceptor::class);

    $t->eq(\DOF\Util\TypeCast::smallint(null, true), 0);
    $t->exceptor(function () {
        \DOF\Util\TypeCast::smallint(null);
    }, \DOF\Util\Exceptor\TypeCastExceptor::class);

    $t->eq(\DOF\Util\TypeCast::smallint(65535), 65535);
    $t->exceptor(function () {
        \DOF\Util\TypeCast::smallint(65536);
    }, \DOF\Util\Exceptor\TypeCastExceptor::class);
    $t->eq(\DOF\Util\TypeCast::smallint(-32768), -32768);
    $t->exceptor(function () {
        \DOF\Util\TypeCast::smallint(-32769);
    }, \DOF\Util\Exceptor\TypeCastExceptor::class);
});

$gwt->unit('Test \DOF\Util\TypeCast::tinyint()', function ($t) {
    $t->exceptor(function () {
        \DOF\Util\TypeCast::tinyint([]);
    }, \DOF\Util\Exceptor\TypeCastExceptor::class);

    $t->eq(\DOF\Util\TypeCast::tinyint(0, false), 0);
    $t->eq(\DOF\Util\TypeCast::tinyint('0'), 0);
    $t->eq(\DOF\Util\TypeCast::tinyint('-1', false), -1);
    $t->eq(\DOF\Util\TypeCast::tinyint(-1, false), -1);

    $t->eq(\DOF\Util\TypeCast::tinyint('', true), 0);
    $t->exceptor(function () {
        \DOF\Util\TypeCast::tinyint('');
    }, \DOF\Util\Exceptor\TypeCastExceptor::class);
    $t->eq(\DOF\Util\TypeCast::tinyint(null, true), 0);
    $t->exceptor(function () {
        \DOF\Util\TypeCast::tinyint(null);
    }, \DOF\Util\Exceptor\TypeCastExceptor::class);
    $t->eq(\DOF\Util\TypeCast::tinyint(255), 255);
    $t->exceptor(function () {
        \DOF\Util\TypeCast::tinyint(256);
    }, \DOF\Util\Exceptor\TypeCastExceptor::class);
    $t->eq(\DOF\Util\TypeCast::tinyint(-128), -128);
    $t->exceptor(function () {
        \DOF\Util\TypeCast::tinyint(-129);
    }, \DOF\Util\Exceptor\TypeCastExceptor::class);
});
