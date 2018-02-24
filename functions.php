<?php
/**
 * Created by PhpStorm.
 * User: dco
 * Date: 2/17/18
 * Time: 1:54 PM
 */

function dbg($var, $mode = 0, $html = 0) {
    if ($mode) {
        if ($html) {
            echo '<pre>';
            var_dump($var);
            echo '</pre>';
        } else {
            var_dump($var);
        }
    } else {
        if ($html) {
            echo '<pre>';
            print_r($var);
            echo '</pre>';
        } else {
            print_r($var);
        }
    }
}