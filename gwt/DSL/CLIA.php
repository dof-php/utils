<?php

$gwt->unit('\DOF\Util\DSL\CLIA::compile()', function ($t) {
    $t->eq(\DOF\Util\DSL\CLIA::compile('dof docs.build --only=http aa --bb cc'), ['dof', 'docs.build', [
        'only' => 'http',
        'bb' => null,
    ], ['aa', 'cc']]);

    $t->eq(\DOF\Util\DSL\CLIA::compile('dof docs.build --only=http -- aa --bb cc'), ['dof', 'docs.build', [
        'only' => 'http',
    ], ['aa', '--bb', 'cc']]);
});
