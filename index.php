<?php

include_once 'Response.php';

$o 			=	new Response( null, 'Asia/Calcutta' );

$k 			=	$o->next('2016-01-01 09:02:30');

$response =	$k->response;
$reasons  =	$k->reasons;
$current  =	$k->current;
$next     =	$k->next;

echo 'Current Date : ',  $current->format('F'), ' ' , $current->format('d'), ' ' , $current->format('l'), ' ' , $current->format('h'), ':' , $current->format('i'), ' ' , $current->format('A'), '<br>' ;
echo 'Response in : ',  $response, '<br>' ;

echo 'Reasons for delay : ';
if ( $reasons )	{

	echo '<ul>';
	foreach ($reasons as $key => $value) {
		
		if ( is_array($value) )	{
			echo '<li>';
			echo $key . ' -> ' . implode(', ', $value);
			echo '</li>';
		}
		else 	{
			echo '<li>';
			echo $key . ' -> ' . $value;
			echo '</li>';
		}
	}
	echo '</ul>';
}


echo 'Response Date : ',  $next->format('F'), ' ' , $next->format('d'), ' ' , $next->format('l'), ' ' , $next->format('h'), ':' , $next->format('i'), ' ' , $next->format('A'), '<br>' ;
