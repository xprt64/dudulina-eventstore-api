<?php
/**
 * @copyright  Copyright (c) Constantin Galbenu xprt64@gmail.com
 * All rights reserved.
 */

namespace Gica\Infrastructure\Http\Lib;


class ArrayToXmlConverter
{
    /**
     * @var string
     */
    private $arrayKeyTag;

    public function __construct(
        string $arrayKeyTag = 'item'
    )
    {
        $this->arrayKeyTag = $arrayKeyTag;
    }

    public function convert($input, string $topTag, string $aditionalAttributes = '')
    {
        $return = '<' . $topTag;

        if ($aditionalAttributes) {
            $return .= ' ' . ltrim($aditionalAttributes);
        }

        if (!is_array($input)) {
            $return .= ' type="xs:' . gettype($input) . '"';
            $return .= '>';

            if (null !== $input) {
                $return .= htmlentities($this->encodeNonArrayValue($input), ENT_QUOTES, 'utf-8');
            }
        } else {
            $return .= '>';
            foreach ($input as $k => $v) {
                $return .= $this->convert($v, is_int($k) ? $this->arrayKeyTag : $k );
            }
        }

        $return .= '</' . $topTag . '>';

        return $return;
    }

    private function encodeNonArrayValue($input)
    {
        if(is_bool($input))
        {
            return $input ? 'true':'false';
        }

        return $input;
    }
}