<?

//均值
function avg($arr){
    return array_sum($arr) / count($arr);
}

//标准差
function stdev($arr){
    $avg = avg($arr);
    $sum = 0;
    foreach($arr as $v){
        $sum += pow($v-$avg, 2);
    }
    $divide = max(count($arr) - 1, 1);
    return pow($sum / $divide, 0.5);
}

//概率密度函数
function normdist($arr, $x){
    $avg = avg($arr);
    $stdev = stdev($arr);

    $xp = - pow($x-$avg, 2) / (2 * pow($stdev, 2));
    $right = exp($xp);

    $left = 1 / ($stdev * pow(2*pi(), 0.5));

    return $left * $right;
}

function intervals($arr){
    $start = round(min($arr) - 0.03, 2);
    $n = ceil(pow(count($arr), 0.5));
    $tick = round((max($arr) - min($arr)) / $n, 2);
    $ret = [];
    while($start <= max($arr)){
        $ret[] = $start;
        $start += $tick;
    }
    $ret[] = $start;
    return $ret;
}

function intervalCount($arr, $intervals){
    $ret = [];
    foreach($intervals as $k=>$i2){
        $i1 = $k > 0 ? $intervals[$k-1] : 0;
        $i2_key = strval($i2);
        !isset($ret[$i2_key]) && $ret[$i2_key] = 0;
        foreach($arr as $val){
            if($val > $i1 && $val <= $i2){
                $ret[$i2_key] += 1;
            }
        }
    }
    return $ret;
}

function normdistLine($arr){
    $intervals = intervals($arr);
    $interval_count = intervalCount($arr, $intervals);
    $ret = [];
    foreach($intervals as $i){
        $i_key = strval($i);
        $ret[$i_key] = normdist($arr, $i);
    }

    return [
        "interval_count_line" => [$interval_count],
        "norm_dist_line" => $ret
    ];
}