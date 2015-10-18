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

    const DEFAULT_OFFICE_CALL_TIME  = 	'PT12H30M';

 	const DEFAULT_TEMPLATE 			=	'Within {{days}} & {{hour}}';

	/**
     * Format to use for __toString method when type juggling occurs.
     *
     * @var string
     */
    protected static $toStringFormat =  self::DEFAULT_TO_STRING_FORMAT;

    protected $weekends              =  array('Sun','Sat');

    protected $holidays              =  array(
                                            '01-01',    // New year's day
                                            '04-03',    // Good friday
                                            '05-01',    // Labour day
                                            '12-25',    // Christmas day
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

    	if ( ( $current->format('H')  < 19 ) && ( ! $this->isSunday( $current ) ) && ( ! $this->isHoliday( $current ) ) )	{

    		$next 	=	 new static( $current->format('Y-m-d ' . static::DEFAULT_OFFICE_CLOSE_TIME), $this->timezone );
    	}
    	else 	{

			$next 		= 	clone $current;
			$next->setTime(12,30);

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

    	$result->text 		=	static::DEFAULT_TEMPLATE;

    	if ( $diff->d == 1 )
    		$result->text 	=	str_replace( '{{days}}', '1 day', $result->text );
    	else if ( $diff->d > 1 )
    		$result->text 	=	str_replace( '{{days}}',  $diff->d . ' days', $result->text );

		if ( $diff->h > 0 )
    		$result->text 		=	str_replace( '{{hour}}', $diff->h . 'hr', $result->text );

    	$result->text 		=	str_replace( '{{days}} & ', '', $result->text );
    	$result->text 		=	str_replace( '{{hour}}', '', $result->text );

    	$result->current 	=	$current;
    	$result->next 		=	$next;

    	return $result;
    }

    private function isHoliday( $current )	{

		if ( in_array( $current->format('m-d'), $this->holidays ) )
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
