<?php

/**
 * Calculates the water capacity in array according to the rule specified by the test
 * Class CalcWaterCapacity
 */
class CalcWaterCapacity
{

    /**
     * Array of numbers to be calculated
     * @var
     */
    private $arr;

    /**
     * Water capacity calculation result
     * @var array
     */
    private $arr_maxs;

    /**
     * Array with the amounts of water in each column and the total
     * @var array
     */
    private $arr_sum;

    /**
     * Phrase used to say the total amount of water in each case
     * @var string
     */
    private $frase = 'Capacidade total de agua é %s.';

    /**
     * DesafioGetIn constructor.
     * Calculates water capacity
     * @param $arr
     */
    function __construct($arr)
    {
        $this->arr = $arr;
        $this->arr_maxs = $this->_searchMaxs($arr); // We look for all the positions that the greatest number presents.
        $this->arr_sum = $this->_calcWaterCapacity($arr); // Calculating water capacity
    }

    /**
     * Search for the largest number and all the positions it appears to subdivide the array into parts
     * @param $arr
     * @return array
     */
    private function _searchMaxs($arr)
    {
        foreach ($arr as $key => $value) {
            if (!isset($arr_maxs) || $value > $arr_maxs['v']) {
                $arr_maxs = ['k' => [$key], 'v' => $value];
                continue;
            }
            if ($value == $arr_maxs['v']) {
                $arr_maxs['k'][] = $key;
            }
        }
        return $arr_maxs;
    }

    /**
     * Calculates water capacity
     * @param $arr
     * @return array
     */
    private function _calcWaterCapacity($arr)
    {
        $last_key_of_arr_maxs = count($arr) - 1; // key of the last element of the array
        // *______________*  or  ______________*  - Higher number only in the [First position] and [Last position] positions or only in the [last]
        if (in_array(0, $this->arr_maxs['k']) && in_array($last_key_of_arr_maxs, $this->arr_maxs['k'])) {
            $sub_arr[] = [ // Range - From [First position] to [Last position] of array
                'from' => 0,
                'to' => $last_key_of_arr_maxs,
                'increment' => 1
            ];

            // *_______________   -  Highest number only in the [first] position
        } elseif (in_array(0, $this->arr_maxs['k']) && count($this->arr_maxs['k']) == 1) {
            $sub_arr[] = [ // Range - From [Last position] to [First position]
                'from' => $last_key_of_arr_maxs,
                'to' => 0,
                'increment' => -1
            ];

            // _____*_______*_____    or    _____*___*___*_____   - The largest number is not present in either the first or the last but there are more than one in the center of the array
        } elseif (!in_array(0, $this->arr_maxs['k']) && !in_array($last_key_of_arr_maxs, $this->arr_maxs['k']) && count($this->arr_maxs['k']) >= 2) {
            $first_max = min($this->arr_maxs['k']); // Lowest position where the highest number was found
            $last_max = max($this->arr_maxs['k']); // Highest position where the highest number was found

            $sub_arr[] = [ // Range - [First position] to [First position where the highest number appears]
                'from' => 0,
                'to' => $first_max,
                'increment' => 1
            ];
            $sub_arr[] = [ // Range - [First position where the highest number appears] to [Last position where the highest number appears]
                'from' => $first_max,
                'to' => $last_max,
                'increment' => 1
            ];
            $sub_arr[] = [ // Range - [Last position where the highest number appears] to [Last position]
                'from' => $last_key_of_arr_maxs,
                'to' => $last_max,
                'increment' => -1
            ];

            // _________*_________   - The largest number appears only once, in the middle of the array.
        } else {
            $sub_arr[] = [ // Range - [First position] to [Highest number]
                'from' => 0,
                'to' => $this->arr_maxs['k'][0],
                'increment' => 1
            ];
            $sub_arr[] = [ // Range - [Last Position] to [Highest Number]
                'from' => $last_key_of_arr_maxs,
                'to' => $this->arr_maxs['k'][0],
                'increment' => -1
            ];
        }

        $arr_sum = ['total' => 0];
        foreach ($sub_arr as $sub_arr_v) {
            $max_num = 0;
            $increment = ($sub_arr_v['from'] < $sub_arr_v['to']) ? 1 : -1;
            for ($i = $sub_arr_v['from']; (($sub_arr_v['from'] < $sub_arr_v['to'] && $i <= $sub_arr_v['to']) || ($sub_arr_v['from'] > $sub_arr_v['to'] && $i >= $sub_arr_v['to'])); $i = $i + $increment) {
                if ($arr[$i] > $max_num) $max_num = $arr[$i];
                if ($i == $sub_arr_v['from']) continue;
                if ($arr[$i] < $max_num) {
                    $arr_sum['arr'][$i] = $max_num - $arr[$i]; // Total water in this column
                    $arr_sum['total'] = $arr_sum['total'] + $arr_sum['arr'][$i]; // Total global water
                }
            }
        }

        return $arr_sum;
    }

    /**
     * Returns total water capacity
     * @return int|mixed
     */
    public function getTotalWaterCapacity()
    {
        return $this->arr_sum['total'];
    }

    /**
     * Detailed returns to water capacity
     * @return array|int[]
     */
    public function getWaterCapacity()
    {
        return $this->arr_sum;
    }

    /**
     * Loops necessary to draw independent of the template
     * @param $func_template
     */
    private function draw($func_template) {
        $total = $this->arr_maxs['v'];
        for ($i = 0; $i < count($this->arr); $i++) {
            $value = $this->arr[$i];
            $black = $this->arr[$i];
            $water = (isset($this->arr_sum['arr'][$i])) ? $this->arr_sum['arr'][$i] : 0;
            $empty = $total - $water - $black;

            if(gettype($func_template)=='object') $func_template($black, $water, $empty, $value, $i);
        }
    }

    /**
     * Draws array columns to be viewed in browser
     */
    public function draw_for_terminal()
    {
        echo sprintf($this->frase, $this->getTotalWaterCapacity()) . "\n";
        $this->draw(function($black, $water, $empty){
            echo str_repeat('[███]', $black);
            echo str_repeat('[ * ]', $water);
            echo str_repeat('[   ]', $empty);
            echo "\n";
        });
    }

    /**
     * Draws the columns of the array to be viewed on the command line
     */
    public function draw_for_web()
    {
        echo '
        <style>
            .case{
                margin-bottom:20px;
                clear: both;
            }
            .column{
                float:left;
            }
            .column > div {
                width: 30px;
                height: 30px;
                border: 1px solid #CCC;
                text-align: center;
                line-height: 30px;
            }
            .black {
                background-color: black;
            }
            .water {
                background-color: lightblue;
            }
        </style>
        ';
        echo '<div class="case">';
        echo '<h3>Total da capacidade é ' . $this->getTotalWaterCapacity() . '</h3>';
        $this->draw(function($black, $water, $empty, $value){
            echo '<div class="column">';
            echo str_repeat('<div></div>', $empty);
            if ($water > 1) echo str_repeat('<div class="water"></div>', $water -1);
            if ($water >= 1) echo '<div class="water">' . $water . '</div>';
            echo str_repeat('<div class="black"></div>', $black);
            echo "<div></div><div>$value</div>";
            echo '</div>';
        });
        echo '</div>';
        echo '<br clear="all" /><br clear="all" /><br clear="all" />';
    }
}

// Cases presented in the test
$arr = [
    [7, 10, 2, 5, 13, 3, 4, 1, 5, 9],
    [5, 4, 3, 2, 1, 2, 3, 4, 5],
    [7, 10, 2, 5, 13, 3, 4, 10, 5, 9, 4, 2, 6, 5, 18, 6, 8, 6, 15, 4, 20, 4, 8, 9, 5, 21, 4, 7, 19, 2],
    [10],
    [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
    [10, 9, 8, 7, 6, 5, 4, 3, 2, 1],
    [10, 10, 10, 10, 10, 10, 10, 10, 10, 10, 10, 10, 10, 10, 10],
    [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 10, 9, 8, 7, 6, 5, 4, 3, 2, 1]
];

// Result for cases
foreach ($arr as $key => $val) {
    $CalcWaterCapacity = new CalcWaterCapacity($val);

    if (php_sapi_name() == 'cli') {
        $CalcWaterCapacity->draw_for_terminal();
    } else {
        $CalcWaterCapacity->draw_for_web();
    }
}


