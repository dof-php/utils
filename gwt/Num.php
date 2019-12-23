<?php

$gwt->unit('Test \DOF\Util\Num::between()', function ($t) {
    $t->false(\DOF\Util\Num::between(2, 0, 1));
    $t->true(\DOF\Util\Num::between(0, 0, 1));
    $t->true(\DOF\Util\Num::between(1, 0, 1));
    $t->true(\DOF\Util\Num::between(0, -1, 1));
    $t->false(\DOF\Util\Num::between(0, 0, 1, false));
    $t->false(\DOF\Util\Num::between(1, 0, 1, false));
});
$gwt->unit('Test \DOF\Util\Num::big()', function ($t) {
    $t->eq(1, \DOF\Util\Num::big('1'));
    $t->eq(1, \DOF\Util\Num::big('1.001'));
    $t->eq(1, \DOF\Util\Num::big('1.001', 2), false);
    $t->neq(1, \DOF\Util\Num::big('1.001', 2));
    $t->eq(\DOF\Util\Num::big(pow(2, 63)), \intval('9223372036854775808'));
    $t->eq(\DOF\Util\Num::big(pow(2, 32)), 4294967296);
    $t->eq(\DOF\Util\Num::big(pow(2, 31)), 2147483648);
    $t->eq(\DOF\Util\Num::big(pow(2.3456789, 10), 8), 5042.96103721);
});
