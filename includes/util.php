<?php 
function dd($var) {
    //dump and die (debugging only)
    echo "<pre>";
    var_dump ($var);
    echo "</pre>";
    die();
}

    function intersects(array $boxAin, array $boxBin): bool

    {
    /* Checks for intersection between two 2D Axis-Aligned Bounding Boxes (AABBs).
     *
     * @param array $boxA An array representing the first bounding box, e.g., ['x1' => 0, 'y1' => 0, 'x2' => 10, 'y2' => 10].
     * @param array $boxB An array representing the second bounding box, e.g., ['x1' => 5, 'y1' => 5, 'x2' => 15, 'y2' => 15].
     * @return bool True if the bounding boxes intersect, false otherwise.
     */  

    //There is inconsistency in the associated indexes (keys) so let's normalize, assuming the arrays are x1, y1, x2, y2 (x is lat, y is lon)
    $boxKeys = explode(" ","min_x min_y max_x max_y");
    $boxAvalues = array_values($boxAin);
    $boxBvalues = array_values($boxBin);
    $box1 = array_combine($boxKeys, $boxAvalues);
    $box2 = array_combine($boxKeys, $boxBvalues);
    
    // Check for overlap on the x-axis
    $x_overlap = ($box1['max_x'] > $box2['min_x'] && $box1['min_x'] < $box2['max_x']);

    // Check for overlap on the y-axis
    $y_overlap = ($box1['max_y'] > $box2['min_y'] && $box1['min_y'] < $box2['max_y']);

    // Intersection occurs if both x and y axes overlap
    return $x_overlap && $y_overlap;
    }

    /*
    // Example Usage:
    $box1 = ['x1' => 0, 'y1' => 0, 'x2' => 10, 'y2' => 10];
    $box2 = ['x1' => 5, 'y1' => 5, 'x2' => 15, 'y2' => 15];
    $box3 = ['x1' => 20, 'y1' => 20, 'x2' => 30, 'y2' => 30];

    if (intersects($box1, $box2)) {
        echo "Box 1 and Box 2 intersect.\n";
    } else {
        echo "Box 1 and Box 2 do not intersect.\n";
    }

    if (intersects($box1, $box3)) {
        echo "Box 1 and Box 3 intersect.\n";
    } else {
        echo "Box 1 and Box 3 do not intersect.\n";
    }
        */

    ?>