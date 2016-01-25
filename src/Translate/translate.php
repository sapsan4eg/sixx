<?php

/**
 * Translate label
 *
 * @param string $label
 * @return string
 */
function _t($label)
{
    return \Sixx\Translate\Mui::get($label);
}