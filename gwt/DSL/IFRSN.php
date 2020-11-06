<?php

use DOF\Util\Arr;
use DOF\Util\DSL\IFRSN;
use DOF\Util\Exceptor\IFRSNExceptor;

// print_r(IFRSN::parse('user(name,post(id,user(id),at{format:1}))'));die;

$gwt->true('Test IRFSN::parse() - normal #1', Arr::eq(IFRSN::parse('user(name)'), ['user' => ['fields' => ['name' => []], 'refs' => []]], false));
$gwt->true('Test IRFSN::parse() - normal #2', Arr::eq(IFRSN::parse('user(name,age)'), ['user' => ['fields' => ['name' => [], 'age' => []], 'refs' => []]], false));
$gwt->true('Test IRFSN::parse() - with arguments #1', Arr::eq(IFRSN::parse('user(at{format:1},name)'), ['user' => ['fields' => ['name' => [], 'at' => ['format' => 1]], 'refs' => []]], false));
$gwt->true('Test IRFSN::parse() - with arguments #2', Arr::eq(
    IFRSN::parse('user(at{format:1,foo:bar},name)'),
    ['user' => ['fields' => ['name' => [], 'at' => ['format' => 1, 'foo' => 'bar']], 'refs' => []]],
    false
));
$gwt->true('Test IRFSN::parse() - with arguments #3 - invalid #1', Arr::eq(
    IFRSN::parse('user(at{format:1,foo=bar},name)'),
    ['user' => ['fields' => ['name' => [], 'at' => 'foo=bar'], 'refs' => []]],
    false
));
$gwt->true('Test IRFSN::parse() - with refs #1', Arr::eq(
    IFRSN::parse('user(name,post(id,user_id))'),
    ['user' => ['fields' => ['name' => []], 'refs' => ['post' => ['refs' => [], 'fields' => ['id' => [], 'user_id' => []]]]]],
    false
));
$gwt->true('Test IRFSN::parse() - with refs and with arguments #1', Arr::eq(
    IFRSN::parse('user(name,post(id,user_id,at{format:1}))'),
    ['user' => ['fields' => ['name' => []], 'refs' => ['post' => ['refs' => [], 'fields' => ['id' => [], 'at' => ['format' => 1], 'user_id' => []]]]]],
    false
));
$gwt->exceptor('Test IRFSN::parse() - with cyclic refs #1', function () {
    IFRSN::parse('user(name,post(id,user(id),at{format:1}))');
}, IFRSNExceptor::class, 'CYCLIC_REFERENCE_FOUND');
$gwt->exceptor('Test IRFSN::parse() - with cyclic refs #2', function () {
    IFRSN::parse('user(name,post(id,user(post(id)),at{format:1}))');
}, IFRSNExceptor::class, 'CYCLIC_REFERENCE_FOUND');
