<?php

/**
* Response
*/
class Response extends DateTime	{

	/**
     * Default format to use for __toString method when type juggling occurs.
     *
     * @var string
     */
    const DEFAULT_TO_STRING_FORMAT 	= 	'Y-m-d H:i:s';

    const DEFAULT_OFFICE_CLOSE_TIME = 	'19:00:00';

    const DEFAULT_OFFICE_CALL_TIME  = 	'12:30:00';

 	const DEFAULT_TEMPLATE 			=	'Within {{days}} & {{hour}}';

	/**
     * Format to use for __toString method when type juggling occurs.
     *
     * @var string
     */
    protected static $toStringFormat =  self::DEFAULT_TO_STRING_FORMAT;

    protected $weekends              =  array('Sun','Sat');

    protected $holidays              =  array(
                                            '01-01' =>  array(
                                                'New year\'s Day'
                                            ),
                                            '04-03' =>  array(
                                                'Good friday'
                                            ),
                                            '05-01' =>  array(
                                                'Labour day'
                                            ),
                                            '12-25' =>  array(
                                                'Christmas day'
                                            ),
                                        );

    /**
     * Create a new Response instance.
     *
     * @param string              $time
     * @param DateTimeZone|string $tz
     */
	function __construct($time = null, $tz = null)	{

		$this->timezone 	=	$tz;
		parent::__construct($time, static::safeCreateDateTimeZone($tz));
	}

    public function next( $datetime = null ) 	{

		$current 	=	new static($datetime, $this->timezone);

		$next 		=	static::nextWorkingDay( $current );

		$diff 		=	$next->diff( $current );

    	return $this->process( $current, $next, $diff );
    }

    private function nextWorkingDay( $current )	{

    	if ( ( $current->format('H')  < 19 ) && ( ! $this->isSunday( $current ) ) && ( ! $this->isWeekend( $current ) ) && ( ! $this->isHoliday( $current ) ) )	{

    		$next 	=	 new static( $current->format('Y-m-d ' . static::DEFAULT_OFFICE_CLOSE_TIME), $this->timezone );
    	}
    	else 	{

            $next   =    new static( $current->format('Y-m-d ' . static::DEFAULT_OFFICE_CALL_TIME), $this->timezone );

			$i 			= 	0; // We have 0 future dates to start with

			while ($i < 1)	{

			    $next->add(new DateInterval('P1D')); // Add 1 day
			    if ( $this->isHoliday( $next ) ) continue; // Don't include year to ensure the check is year independent
			    // Note that you may need to do more complicated things for special holidays that don't use specific dates like "the last Friday of this month"
			    if ( $this->isWeekend( $next ) ) continue;
			    // These next lines will only execute if continue isn't called for this iteration
			    $i++;
			}
    	}

    	return $next;
    }

    private function process( $current, $next, $diff )	{

    	$result 			=	new stdClass;

    	$result->response 		=	static::DEFAULT_TEMPLATE;

    	if ( $diff->d == 1 )
    		$result->response 	=	str_replace( '{{days}}', '1 day', $result->response );
    	else if ( $diff->d > 1 )
    		$result->response 	=	str_replace( '{{days}}',  $diff->d . ' days', $result->response );

		if ( $diff->h > 0 )
    		$result->response 		=	str_replace( '{{hour}}', $diff->h . 'hr', $result->response );

    	$result->response 		=	str_replace( '{{days}} & ', '', $result->response );
    	$result->response 		=	str_replace( '{{hour}}', '', $result->response );

        $result->reasons =   array();
        $i               =   0; // We have 0 future dates to start with
        $temp            =  clone $current;
        do{

            if ( $this->isHoliday( $temp ) ) $result->reasons[$temp->format('m-d')]  = $this->holidays[$temp->format('m-d')];
            if ( $this->isWeekend( $temp ) ) $result->reasons[$temp->format('m-d')]  = $temp->format('l');
            $temp->add(new DateInterval('P1D'));
            $i++;
        }while ($i < $diff->d);

    	$result->current 	=	$current;
    	$result->next 		=	$next;

    	return $result;
    }

    private function isHoliday( $current )	{

		if ( array_key_exists( $current->format('m-d'), $this->holidays ) )
	        return true;

	    return false;
	}

    private function isSunday( $current )   {

        if ( $current->format('w') == 0) {
            return true;
        }
        return false;
    }

    private function isWeekend( $current )	{

	    if ( in_array($current->format('D'), $this->weekends) ) {
	        return true;
	    }
	    return false;
	}

	/**
     * Creates a DateTimeZone from a string or a DateTimeZone
     *
     * @param DateTimeZone|string|null $object
     *
     * @return DateTimeZone
     *
     * @throws InvalidArgumentException
     */
    protected static function safeCreateDateTimeZone($object)	{

        if ($object === null) {
            // Don't return null... avoid Bug #52063 in PHP <5.3.6
            return new DateTimeZone(date_default_timezone_get());
        }
        if ($object instanceof DateTimeZone) {
            return $object;
        }
        $tz = timezone_open((string) $object);
        if ($tz === false) {
            throw new InvalidArgumentException('Unknown or bad timezone ('.$object.')');
        }
        return $tz;
    }

	public function __toString()	{

		return $this->format(static::$toStringFormat);
	}
}
