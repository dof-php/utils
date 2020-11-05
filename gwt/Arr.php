<?php

$gwt->unit('\DOF\Util\Arr::mask()', function ($t) {
    $t->eq(\DOF\Util\Arr::mask(['password' => 123456]), ['password' => '*']);
    $t->eq(\DOF\Util\Arr::mask(['pswd' => '123456'], []), ['pswd' => '123456']);
    $t->eq(\DOF\Util\Arr::mask(['pswd' => '123456'], ['pswd'], '-'), ['pswd' => '-']);
    $t->eq(\DOF\Util\Arr::mask(['password' => 123456, ['secret' => 'secret']]), ['password' => '*', ['secret' => '*']]);
});

$gwt->unit('\DOF\Util\Arr::partition()', function ($t) {
    $t->eq(\DOF\Util\Arr::partition(['redis-1'], 'user'), 'redis-1');
    $t->eq(\DOF\Util\Arr::partition(['redis-1', 'redis-2'], 'user'), 'redis-2');
    $t->eq(\DOF\Util\Arr::partition(['redis-1', 'redis-2', 'redis-3'], 'user'), 'redis-3');
    $t->eq(\DOF\Util\Arr::partition(['a' => 'redis-1', 'c' => 'redis-2', 'b' => 'redis-3'], 'user'), 'redis-2');
});

$gwt->unit('Test \DOF\Util\Arr::cast()', function ($t) {
    $t->eq(\DOF\Util\Arr::cast(null), []);
    $t->eq(\DOF\Util\Arr::cast(' '), []);
    $t->eq(\DOF\Util\Arr::cast(''), []);
    $t->eq(\DOF\Util\Arr::cast([1]), [1]);
    $t->eq(\DOF\Util\Arr::cast(false), [false]);
});

$gwt->unit('Test \DOF\Util\Arr::remove()', function ($t) {
    $t->eq(\DOF\Util\Arr::remove([1, 2, 3, null, '', ' '], [1, 3, 5]), [0 => 1, 2 => 3, 4 => '']);
    $t->eq(\DOF\Util\Arr::remove([1, 2, 3, null, '', ' '], [1, 3, 5], true), [0 => 1, 2 => 3, 4 => '']);
    $t->eq(\DOF\Util\Arr::remove([1, 2, 3, null, '', ' '], [1, 3, 5], false), [1, 3, '']);
});

$gwt->unit('Test \DOF\Util\Arr::unset()', function ($t) {
    $t->eq(function () {
        $data = [1, 2, 3, null, '', ' '];
        \DOF\Util\Arr::unset($data, [1, 3, 5]);
        return $data;
    }, [0 => 1, 2 => 3, 4 => '']);

    $t->eq(function () {
        $data = [1, 2, 3, null, '', ' '];
        \DOF\Util\Arr::unset($data, [1, 3, 5], true);
        return $data;
    }, [0 => 1, 2 => 3, 4 => '']);

    $t->eq(function () {
        $data = [1, 2, 3, null, '', ' '];
        \DOF\Util\Arr::unset($data, [1, 3, 5], false);
        return $data;
    }, [1, 3, '']);
});

$gwt->unit('Test \DOF\Util\Arr::id()', function ($t) {
    $t->eq(\DOF\Util\Arr::id([1, 2, 3, null, '', ' ']), [1, 2, 3]);
    $t->eq(\DOF\Util\Arr::id([1, 2, '3b', null, '', ' ']), [1, 2]);
    $t->eq(\DOF\Util\Arr::id([1, '2', '3', 2, null, '', ' ']), [1, 2, 3, 2]);
    $t->eq(\DOF\Util\Arr::id([1, '2', '3', 2, null, '', ' '], true), [1, 2, 3]);
});

$gwt->unit('Test \DOF\Util\Arr::match()', function ($t) {
    $t->eq(\DOF\Util\Arr::match(['id', 'uid', 'user_id', 1122], ['uid' => 2], -1), 2);
    $t->eq(\DOF\Util\Arr::match(['id', 'uid', 'user_id', 1122], ['uidx' => 2], -1), -1);
    $t->eq(function () {
        $key = null;
        \DOF\Util\Arr::match(['id', 'uid', 'user_id', 1122], ['uid' => 2], -1, $key);
        return $key;
    }, 'uid');
});

$gwt->unit('Test \DOF\Util\Arr::str()', function ($t) {
    $t->eq(\DOF\Util\Arr::str([1, 2, 3], ','), '1,2,3');
    $t->eq(\DOF\Util\Arr::str([1, 2, 3, null, '', ' '], ','), '1,2,3');
});

$gwt->unit('Test \DOF\Util\Arr::union()', function ($t) {
    $t->eq(\DOF\Util\Arr::union([], [], []), []);
    $t->eq(\DOF\Util\Arr::union([1, 2, 3], [2, 3, 4]), [1, 2, 3, 4]);
    $t->eq(\DOF\Util\Arr::union([1, 2], [2, 3], [4]), [1, 2, 3, 4]);
});

$gwt->eq('Test \DOF\Util\Arr::get(): #1', function ($tester) {
    return \DOF\Util\Arr::get('a.b.c', ['a.b.c' => 42]);
}, 42);

$gwt->eq('Test \DOF\Util\Arr::get(): #2', function ($tester) {
    return \DOF\Util\Arr::get('a.b.c.d', ['a.b.c' => 42], 'foo');
}, 'foo');

$gwt->eq('Test \DOF\Util\Arr::get(): #3', function ($tester) {
    return \DOF\Util\Arr::get('a.b.c', [
        'a' => [
            'b.c' => 42,
        ],
    ]);
}, 42);

$gwt->eq('Test \DOF\Util\Arr::get(): #4', function ($tester) {
    return \DOF\Util\Arr::get('a.b.c', [
        'a' => [
            'b' => [
                'c' => 42,
            ],
        ],
    ]);
}, 42);

$gwt->eq('Test \DOF\Util\Arr::get(): #5', function ($tester) {
    return \DOF\Util\Arr::get('a.b.c', [
        'a.b' => [
            'c' => 42,
        ],
    ], 'foo');
}, 'foo');

$gwt->eq('Test \DOF\Util\Arr::get(): #6', function ($tester) {
    return \DOF\Util\Arr::get('a.b.c', [
        'a.b' => [
            'c' => 42,
        ],
    ], 'foo', ',');
}, 'foo');

$gwt->eq('Test \DOF\Util\Arr::get(): #7', function ($tester) {
    return \DOF\Util\Arr::get('a-b-c', [
        'a' => [
            'b-c' => 42,
        ],
    ], 'foo', '-');
}, 42);

$gwt->eq('Test \DOF\Util\Arr::get(): #8', function ($tester) {
    return \DOF\Util\Arr::get('a-b-c-d-e-f-g', [
        'a' => [
            'b' => [
                'c' => [
                    'd' => [
                        'e' => [
                            'f' => [
                                'g' => 42,
                            ],
                        ],
                    ]
                ],
            ],
        ],
    ], 'foo', '-');
}, 42);

$gwt->eq('Test \DOF\Util\Arr::get(): #9', function ($tester) {
    return \DOF\Util\Arr::get('a-b-c-d-e-f-g', [
        'a' => [
            'b-c' => [
                'd' => [
                    'e' => [
                        'f' => [
                            'g' => 42,
                        ],
                    ],
                ]
            ],
        ],
    ], 'foo', '-');
}, 'foo');

$gwt->null('Test \DOF\Util\Arr::get(): #10', function ($tester) {
    return \DOF\Util\Arr::get('a.b.c', []);
});

$gwt->null('Test \DOF\Util\Arr::get(): #11', function ($tester) {
    return \DOF\Util\Arr::get('', ['a.b.c' => 42]);
});

$gwt->eq('Test \DOF\Util\Arr::load #1: File not exists', function ($tester) {
    return \DOF\Util\Arr::load('/a/b/c/d/e/f/g/h/i/j/k/l/m/n.php', false);
}, []);
$gwt->exceptor('Test \DOF\Util\Arr::load #2: File not exists', function ($tester) {
    \DOF\Util\Arr::load('/a/b/c/d/e/f/g/h/i/j/k/l/m/n.php');
});

$gwt->true('Test \DOF\Util\Arr::save(): #1', function ($tester) {
    $data = ['foo' => 'bar', 'null' => null, 'bool' => true, 1, [0.1], 'a' => '"', 'b' => "'", 'c' => '\'"'];

    $path = \DOF\Util\FS::path('tmp', 'test-arr-save.'.\md5(\microtime().\mt_rand(0, 1000)));

    \DOF\Util\Arr::save($data, $path);

    $load = \DOF\Util\Arr::load($path);
    $load['a'] = stripslashes($load['a']);
    $load['b'] = stripslashes($load['b']);
    $load['c'] = stripslashes($load['c']);

    \DOF\Util\FS::unlink($path);

    return $load === $data;
});

$gwt->true('Test \DOF\Util\Arr::save(): #2', function ($tester) {
    $data = ['foo' => 'bar', 'null' => null, 'bool' => true, 1, [0.1], 'a' => '"', 'b' => "'", 'c' => '\'"'];

    $path1 = \DOF\Util\FS::path('tmp', 'test-arr-save-1.'.\md5(\microtime().\mt_rand(0, 1000)));
    $path2 = \DOF\Util\FS::path('tmp', 'test-arr-save-2.'.\md5(\microtime().\mt_rand(0, 1000)));

    \DOF\Util\Arr::save($data, $path1, true);
    \DOF\Util\Arr::save($data, $path2, false);

    $res = \filesize($path1) < \filesize($path2);

    \DOF\Util\FS::rmdir('tmp');
    \DOF\Util\FS::unlink($path1);
    \DOF\Util\FS::unlink($path2);

    return $res;
});

$gwt->eq('Test \DOF\Util\Arr::trim(): #1', function ($tester) {
    $arr = [
        'a' => 0, 'b' => null, 'c' => false, 'd' => '', 'e' => ['a' => null, 'b' => '', 'c' => []], 'f' => ' ', 'g' => '0'
    ];

    return \DOF\Util\Arr::trim($arr, false);
}, [0, false, '0']);

$gwt->eq('Test \DOF\Util\Arr::trim(): #2', function ($tester) {
    $arr = [
        'a' => 0, 'b' => null, 'c' => false, 'd' => '', 'e' => ['a' => null, 'b' => '', 'c' => []], 'f' => ' ', 'g' => '0'
    ];

    return \DOF\Util\Arr::trim($arr, true);
}, ['a' => 0, 'c' => false, 'g' => '0']);

$gwt->unit('Test \DOF\Util\Arr::first()', function ($t) {
    $t->eq(\DOF\Util\Arr::first(['aa', 'bb']), 'aa');
    $t->eq(\DOF\Util\Arr::first(['aa', 'bb' => 'cc']), 'aa');
    $t->eq(\DOF\Util\Arr::first('AA\\BB\\CC', '\\'), 'AA');
    $t->eq(\DOF\Util\Arr::first(new class {
        public function __toArray()
        {
            return ['aa', 'bb'];
        }
    }), 'aa');
    $t->null(\DOF\Util\Arr::first(new class {
        public function __toArray()
        {
            return 'not array';
        }
    }));
});

$gwt->unit('Test \DOF\Util\Arr::last()', function ($t) {
    $t->eq(\DOF\Util\Arr::last(['aa', 'bb']), 'bb');
    $t->null(\DOF\Util\Arr::last(['aa', 'bb' => 'cc']), 'bb');
    $t->eq(\DOF\Util\Arr::last('AA\\BB\\CC', '\\'), 'CC');
    $t->eq(\DOF\Util\Arr::last(new class {
        public function __toArray()
        {
            return ['aa', 'bb'];
        }
    }), 'bb');
    $t->null(\DOF\Util\Arr::last(new class {
        public function __toArray()
        {
            return 'not array';
        }
    }));
});

$gwt->unit('Test \DOF\Util\Arr::eq()', function ($t) {
    $t->true(\DOF\Util\Arr::eq(['aa' => '11', 'bb' => '22'], ['bb' => '22', 'aa' => '11'], false));
    $t->false(\DOF\Util\Arr::eq(['aa' => '11', 'bb' => '22'], ['bb' => '22', 'aa' => '11']));
    $t->true(\DOF\Util\Arr::eq(['aa', 'bb'], ['bb', 'aa'], false));
    $t->true(\DOF\Util\Arr::eq(['aa', 'bb', '', null], ['bb', 'aa'], false, true));
});
