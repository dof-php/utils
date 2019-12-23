<?php

$gwt->unit('\DOF\Util\Surrogate\Validator::parse()', function ($t) {
    $t->exceptor(function () {
        \DOF\Util\Surrogate\Validator::parse([1,2,3]);
    }, \DOF\Util\Exceptor\ValidatorExceptor::class, 'INVALID_VALIDATE_KEY');
    $t->exceptor(function () {
        \DOF\Util\Surrogate\Validator::parse([true,false,null]);
    }, \DOF\Util\Exceptor\ValidatorExceptor::class, 'INVALID_VALIDATE_KEY');
    $t->exceptor(function () {
        \DOF\Util\Surrogate\Validator::parse([
            'email' => [1],
        ]);
    }, \DOF\Util\Exceptor\ValidatorExceptor::class, 'INVALID_VALIDATE_RULE');
    $t->exceptor(function () {
        \DOF\Util\Surrogate\Validator::parse([
            'email' => [[]],
        ]);
    }, \DOF\Util\Exceptor\ValidatorExceptor::class, 'INVALID_VALIDATE_RULE');
    $t->exceptor(function () {
        \DOF\Util\Surrogate\Validator::parse([
            'email' => [true],
        ]);
    }, \DOF\Util\Exceptor\ValidatorExceptor::class, 'INVALID_VALIDATE_RULE');
    $t->exceptor(function () {
        \DOF\Util\Surrogate\Validator::parse([
            'email' => [''],
        ]);
    }, \DOF\Util\Exceptor\ValidatorExceptor::class, 'INVALID_VALIDATE_RULE');
    $t->exceptor(function () {
        \DOF\Util\Surrogate\Validator::parse([
            'email' => [' '],
        ]);
    }, \DOF\Util\Exceptor\ValidatorExceptor::class, 'INVALID_VALIDATE_RULE');
    $t->exceptor(function () {
        \DOF\Util\Surrogate\Validator::parse([
            'email' => [
                'type',
                1
            ],
        ]);
    }, \DOF\Util\Exceptor\TypeHintExceptor::class, 'UNTYPEHINTABLE_TYPE');
    $t->exceptor(function () {
        \DOF\Util\Surrogate\Validator::parse([
            'email' => [
                'type' => null,
            ],
        ]);
    }, \DOF\Util\Exceptor\TypeHintExceptor::class, 'UNTYPEHINTABLE_TYPE');
    $t->exceptor(function () {
        \DOF\Util\Surrogate\Validator::parse([
            'email' => [
                'type' => true,
            ],
        ]);
    }, \DOF\Util\Exceptor\TypeHintExceptor::class);
    $t->exceptor(function () {
        \DOF\Util\Surrogate\Validator::parse([
            'email' => [
                'type' => 'xxxx',
            ],
        ]);
    }, \DOF\Util\Exceptor\TypeHintExceptor::class);
    $t->exceptor(function () {
        \DOF\Util\Surrogate\Validator::parse([
            'email' => [
                'type' => 'string',
                'need' => 1,
                'needifno' => 'username',
            ],
        ]);
    }, \DOF\Util\Exceptor\ValidatorExceptor::class, 'MULTIPLE_REQUIREMENT_RULES');
    $t->eq(\DOF\Util\Surrogate\Validator::parse([
        'email' => [
            'type' => 'string',
        ],
    ]), [
        'email' => [
            'type' => 'string',
        ]
    ]);
    $t->eq(\DOF\Util\Surrogate\Validator::parse([
        'email' => [
            'type' => 'string',
            'need' => 1,
            'default' => 'support@dof.org',
            'min' => 12,
        ],
    ]), [
        'email' => [
            'type' => 'string',
            'require' => ['need', 1],
            'default' => 'support@dof.org',
            'normal' => [
                'min' => 12,
            ],
        ]
    ]);
    $t->eq(\DOF\Util\Surrogate\Validator::parse([]), []);
});

$gwt->unit('\DOF\Util\Surrogate\Validator::throw()', function ($t) {
    $t->eq(function () {
        \DOF\Util\Surrogate\Validator::setData([
        ])->setRules([
            'email' => [
                'need' => 0,
            ],
        ])->execute();

        return true;
    }, true);
    $t->eq(function () {
        try {
            \DOF\Util\Surrogate\Validator::setData([
            ])->setRules([
                'email' => [
                    'need' => 1,
                ],
            ])->execute();
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }, 'Missing or empty parameter: `email`. (need: 1)');
    $t->eq(function () {
        try {
            \DOF\Util\Surrogate\Validator::setData([
            ])->setRules([
                'email' => [
                    'need' => 1,
                ],
            ])->setErrs([
                'email' => [
                    'need' => '请填写电子邮箱',
                ],
            ])->execute();
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }, '请填写电子邮箱');
    $t->eq(function () {
        try {
            $validator = \DOF\Util\Surrogate\Validator::setData([
                'email' => true,
            ])->setRules([
                'email' => [
                    'type' => 'string',
                ],
            ])->execute();
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }, 'Unacceptable type: `email` is not string. (boolean: true)');
    $t->eq(function () {
        try {
            $validator = \DOF\Util\Surrogate\Validator::setData([
                'email' => true,
            ])->setRules([
                'email' => [
                    'type' => 'string',
                ],
            ])->setExtra([
                'email' => [
                    'TYPE' => [
                        'ERR' => '电子邮箱格式必须是字符串 (:value:)',
                        'ERR_PREG' => 1,
                    ],
                ],
            ])->execute();
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }, '电子邮箱格式必须是字符串 (boolean: true)');
    $t->eq(function () {
        try {
            $validator = \DOF\Util\Surrogate\Validator::setData([
                'email' => 'a@b.c',
            ])->setRules([
                'email' => [
                    'type' => 'string',
                    'min' => 6,
                ],
            ])->execute();
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }, 'Validation failed: min(6, email). (a@b.c: 5)');
});

$gwt->unit('\DOF\Util\Surrogate\Validator::execute()', function ($t) {
    $t->eq(function () {
        $validator = \DOF\Util\Surrogate\Validator::setData([
            'ok' => '0',
        ])->setRules([
            'id' => [
                'default' => '1',
                'type' => 'pint',
            ],
            'ok' => [
                'type' => 'bint',
            ],
        ])->execute();

        return $validator->getResult();
    }, ['id' => 1, 'ok' => 0], false);

    $t->eq(function () {
        $validator = \DOF\Util\Surrogate\Validator::setData([
            'id' => '1',
            'ok' => '0',
        ])->setRules([
            'id' => [
                'type' => 'pint',
            ],
            'ok' => [
                'type' => 'bint',
            ],
        ])->execute();

        return $validator->getResult();
    }, ['id' => 1, 'ok' => 0]);
});
