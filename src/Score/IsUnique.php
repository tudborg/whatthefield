<?php

namespace WhatTheField\Score;

use \DOMNode;
use WhatTheField\QueryUtils;

class IsUnique implements IScore
{
    static protected $pathCache = [];

    protected $exponent;

    public function __construct($exponent=1)
    {
        $this->exponent = $exponent;
    }

    /**
     * Check if DOMNode is unique (compared to it's siblings)
     */
    public function __invoke(DOMNode $node)
    {
        $utils = QueryUtils::instance();
        $nodePath = $utils->toXPath($node);

        // as all nodes with the same path will have the same score,
        // we cache pr. path name
        if (!isset(self::$pathCache[$nodePath])) {
            $collection = FluentDOM($node->ownerDocument)->find($nodePath);
            $grouped = $utils->groupByContentHash($collection);
            
            $totalCount = count($collection);
            $uniqueCount = count($grouped);

            // special case for uniqueCount 1:
            // not unique at all, return 0
            if ($uniqueCount === 1) {
                $score = 0;
            } elseif ($this->exponent === 1) {
                $score = $uniqueCount / $totalCount;
            } else {
                $score = pow($uniqueCount / $totalCount, $this->exponent);
            }
            self::$pathCache[$nodePath] = $score;
        }
        return self::$pathCache[$nodePath];

    }
}