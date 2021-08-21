<?php

/**
 * works for json objects. will replace all
 * values with the result of the
 * closure.
 */
function walk_recursive($obj, $closure)
{
    if (is_object($obj)) {
        $newObj = new stdClass();
        foreach ($obj as $property => $value) {
            // $newProperty = $closure($property);
            $newValue = walk_recursive($value, $closure);
            $newObj->$newProperty = $newValue;
        }
        return $newObj;
    } elseif (is_array($obj)) {
        $newArray = array();
        foreach ($obj as $key => $value) {
            // $key = $closure($key);
            $newArray[$key] = walk_recursive($value, $closure);
        }
        return $newArray;
    } else {
        return $closure($obj);
    }
}
