<?php

declare(strict_types=1);

namespace DOF\Util\DSL;

use DOF\Util\Exceptor\IFRSNExceptor;

/**
 * IFRSN: Input Fields Relation Structured Notation
 */
final class IFRSN
{
    public static function parse(string $str) : array
    {
        return IFRSN::parseInputGrammerResult(IFRSN::parseInputGrammer($str));
    }

    public static function parseInputGrammer(string $str) : array
    {
        $str = \trim($str);
        $arr = \explode(';', $str);
        $res = [];
        foreach ($arr as $item) {
            $item = \trim($item);
            if (! $item) {
                continue;
            }

            $res[] = IFRSN::parseSentenceGrammer($item);
        }

        return $res;
    }

    public static function parseParameterGrammer(string $parameter) : array
    {
        $parameter = \trim($parameter);
        if (! $parameter) {
            return [];
        }

        $arr = \str_split($parameter, 1);
        $bracesLeft = $bracesRight = [];
        foreach ($arr as $idx => $char) {
            if ($char === '{') {
                $bracesLeft[] = $idx;
                continue;
            }
            if ($char === '}') {
                $bracesRight[] = $idx;
                continue;
            }
        }
        $leftCnt  = \count($bracesLeft);
        $rightCnt = \count($bracesRight);
        if ($leftCnt !== $rightCnt) {
            throw new IFRSNExceptor('INPUT_FIELDS_PARAMETER_GRAMMER_ERROR', [
                'error' => 'INPUT_BRACES_MISMATCH',
                'parameter' => $parameter,
                'count' => [
                    'left'  => $leftCnt,
                    'right' => $rightCnt,
                ],
            ]);
        }

        $braces = IFRSN::adjustBraces($bracesLeft, $bracesRight);

        return [
            'braces' => $braces,
            'string' => $parameter,
            'array'  => $arr,
        ];
    }

    public static function parseSentenceGrammer(string $sentence) : array
    {
        $sentence = \trim($sentence);
        if (! $sentence) {
            return [];
        }

        $arr = \str_split($sentence, 1);
        $parenthesesLeft = $parenthesesRight = $bracesLeft = $bracesRight = [];
        foreach ($arr as $idx => $char) {
            if ($char === '(') {
                $parenthesesLeft[] = $idx;
                continue;
            }
            if ($char === ')') {
                $parenthesesRight[] = $idx;
                continue;
            }
        }
        $leftCnt  = \count($parenthesesLeft);
        $rightCnt = \count($parenthesesRight);
        if ($leftCnt !== $rightCnt) {
            throw new IFRSNExceptor('INPUT_FIELDS_SENTENCE_GRAMMER_ERROR', [
                'error' => 'INPUT_PARENTHESES_MISMATCH',
                'sentecne' => $sentence,
                'count' => [
                    'left'  => $leftCnt,
                    'right' => $rightCnt,
                ],
            ]);
        }

        $parentheses = IFRSN::adjustParentheses($parenthesesLeft, $parenthesesRight);

        return [
            'parentheses' => $parentheses,
            'string' => $sentence,
            'array'  => $arr,
        ];
    }

    public static function adjustBraces(array $left, array $right) : array
    {
        return IFRSN::adjustBrackets($left, $right, '{', '}');
    }

    public static function adjustParentheses(array $left, array $right) : array
    {
        return IFRSN::adjustBrackets($left, $right, '(', ')');
    }

    public static function adjustBrackets(
        array $left,
        array $right,
        string $charLeft,
        string $charRight
    ) : array {
        $_left  = array_flip($left);
        $_right = array_flip($right);
        $all = \array_merge($left, $right);
        sort($all);

        $res = [];
        $log = [];
        $cnt = \count($all);
        for ($i = 0; $i < $cnt; ++$i) {
            $now = $all[$i];
            if (isset($log[$now])) {
                continue;
            }
            $_now = isset($_left[$now]) ? $charLeft : $charRight;
            $lenNow = $lenCompare = 0;
            for ($j = $i + 1; $j < $cnt; ++$j) {
                $compare  = $all[$j];
                if (isset($log[$compare])) {
                    continue;
                }
                $_compare = isset($_left[$compare]) ? $charLeft : $charRight;

                if ($_now === $_compare) {
                    ++$lenNow;
                    continue;
                }
                ++$lenCompare;
                if ($lenCompare > $lenNow) {
                    $log[$now] = $log[$compare] = true;
                    if ($_now === $charLeft) {
                        $res[$now] = $compare;
                    } else {
                        $res[$compare] = $now;
                    }
                    break;
                }
            }
        }

        return $res;
    }

    public static function parseInputGrammerResult(array $result) : array
    {
        $res = [];

        foreach ($result as $sentence) {
            $senres = IFRSN::parseSentenceData($sentence);
            if (! $senres) {
                continue;
            }

            list($name, $children) = $senres;
            $res[$name] = $children;
        }

        return $res;
    }

    /**
     * Parse parameter content
     *
     * @param string $content
     * @return mixed{array|string|null}
     */
    public static function parseParameterContent(string $content)
    {
        $data   = IFRSN::parseParameterGrammer($content);
        $braces = $data['braces'] ?? [];
        $array  = $data['array']  ?? [];
        $result = [];
        foreach ($braces as $idxLeft => $idxRight) {
            if (($array[$idxLeft] ?? false) !== '{') {
                continue;
            }
            $kv = IFRSN::parseKVData($idxLeft, $idxRight, $array, $content);
            if (! $kv) {
                continue;
            }
            list($name, $item) = $kv;
            $item = \array_unique(array_\trim(\explode(',', $item)));
            $result[$name] = $item;
        }
        $contentLeft = \join('', $array);
        $params = \explode(',', $contentLeft);

        foreach ($params as $param) {
            if (! \is_string($param)) {
                continue;
            }
            $param = \trim($param);
            if (! $param) {
                continue;
            }
            $kvs = \explode(':', $param);

            $cnt = \count($kvs);
            // If field has only one pure value parameter
            // return that parameter as value of the field key
            if ($cnt === 1) {
                return $param;
            }
            if ($cnt === 2) {
                // If field parameter has extractly 2 parameters
                // Treat them as a classic KV structure
                list($key, $val) = $kvs;
                $result[$key] = $val;
            } else {
                // If no key of field parameter
                // Treat them as pure values list of the field name
                $result[] = $param;
            }
        }

        return $result;
    }

    public static function cyclicReferenceCheck(array $refs)
    {
        // first
        foreach ($refs as $name => $item) {
            if ($_refs = ($item['refs'] ?? [])) {
                // second/middle
                foreach ($_refs as $_name => $_item) {
                    if ($__refs = ($_item['refs'] ?? [])) {
                        // third/last
                        foreach ($__refs as $__name => $__item) {
                            if (\strtolower($name) === \strtolower($__name)) {
                                throw new IFRSNExceptor('CYCLIC_REFERENCE_FOUND', ['cyclic' => "{$name}.{$_name}.{$__name}"]);
                            }
                        }
                        self::cyclicReferenceCheck($__refs);
                    }
                }
                self::cyclicReferenceCheck($_refs);
            }
        }
    }

    public static function parseSentenceContent(string $content) : ?array
    {
        $content = \trim($content);
        if (! $content) {
            return [];
        }
        $contentData = IFRSN::parseSentenceGrammer($content);
        $parentheses = $contentData['parentheses'] ?? [];
        $charArray   = $contentData['array'] ?? [];
        $len = \mb_strlen($content);
        $res = [
            'refs'   => [],
            'fields' => [],
        ];
        foreach ($parentheses as $idxStart => $idxEnd) {
            if ($charArray[$idxStart] !== '(') {
                continue;
            }
            $kv = IFRSN::parseKVData($idxStart, $idxEnd, $charArray, $content);
            if (! $kv) {
                continue;
            }
            list($name, $item)  = $kv;
            $res['refs'][$name] = IFRSN::parseSentenceContent($item);

            self::cyclicReferenceCheck($res['refs']);
        }

        $fieldStr = \join('', $charArray);
        if (! $fieldStr) {
            return $res;
        }

        $fieldData = IFRSN::parseParameterGrammer($fieldStr);
        $braces  = $fieldData['braces'] ?? [];
        $charArr = $fieldData['array']  ?? [];
        foreach ($braces as $idxLeft => $idxRight) {
            if ($charArr[$idxLeft] !== '{') {
                continue;
            }
            $kv = IFRSN::parseKVData($idxLeft, $idxRight, $charArr, $fieldStr);
            if (! $kv) {
                continue;
            }
            list($name, $item)  = $kv;
            $res['fields'][$name] = IFRSN::parseParameterContent($item);
        }

        $fieldsLeft = \array_filter(\explode(',', \join('', $charArr)));
        foreach ($fieldsLeft as $key) {
            $res['fields'][$key] = [];
        }

        return $res;
    }

    public static function parseKVData(
        int $idxStart,
        int $idxEnd,
        array &$contentArr,
        string $contentStr = null
    ) : ?array {
        $contentStr = $contentStr ?: \join('', $contentArr);

        $key = \mb_strcut($contentStr, 0, $idxStart);
        if (! $key) {
            return null;
        }
        if (false !== ($idx = mb_strripos($key, ','))) {
            $key = \mb_strcut($key, $idx+1);
        }
        $val = \mb_strcut($contentStr, $idxStart+1, $idxEnd-$idxStart-1);
        $keyLen = \mb_strlen($key);
        $idxReplaceStart  = $idxStart - $keyLen;
        $idxReplaceLength = $idxEnd - $idxStart + $keyLen + 1;
        $arrReplace = \array_fill($idxReplaceStart, $idxReplaceLength, '');
        $contentArr = \array_replace($contentArr, $arrReplace);

        return [$key, $val];
    }

    public static function parseSentenceData(array $sentence) : ?array
    {
        $parentheses = $sentence['parentheses'] ?? [];
        $sentenceArr = $sentence['array'] ?? [];

        $res = [];
        foreach ($parentheses as $idxLeft => $idxRight) {
            $name = \join('', \array_slice($sentenceArr, 0, $idxLeft));
            $item = \join('', \array_slice($sentenceArr, $idxLeft+1, $idxRight-$idxLeft-1));
            if (false !== ($idx = mb_strripos($name, ','))) {
                $name = \mb_strcut($name, $idx+1);
            }

            return [$name, IFRSN::parseSentenceContent($item)];
        }

        return $res;
    }
}
