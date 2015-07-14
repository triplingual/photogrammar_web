<?php

function get_post($var)
{
	return filter_input(INPUT_GET, $var, FILTER_SANITIZE_STRING);
}

function sanitize_int($var)
{
	return filter_input(INPUT_GET, $var, FILTER_SANITIZE_NUMBER_INT);
}

?>