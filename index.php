<?php

include_once 'Response.php';


$o 			=	new Response( null, 'Asia/Calcutta' );

$k 			=	$o->next();

$text 		=	$k->text;
$current  	=	$k->current;
$next 		=	$k->next;

echo $current->format('F'), ' ' , $current->format('d'), ' ' , $current->format('l'), ' ' , $current->format('h'), ':' , $current->format('i'), ' ' , $current->format('A'), '<br>' ;
echo $text, '<br>' ;
echo $next->format('F'), ' ' , $next->format('d'), ' ' , $next->format('l'), ' ' , $next->format('h'), ':' , $next->format('i'), ' ' , $next->format('A'), '<br>' ;
