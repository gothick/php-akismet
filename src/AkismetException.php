<?php
namespace Gothick\AkismetClient;

/**
 * Specialised exception class for all Akismet client errors.
 * @author matt
 *
 */
class Exception extends \Exception
{
	// TODO: If this is all we end up doing, do we need this class at all? Might help 
	// to distinguish our known errors from more serious errors in testing, I suppose...
	public function __construct($message, $code = 0, \Exception $previous = null)
	{
		parent::__construct('Gothick\AkismetClient: ' . $message, $code,
				$previous);
	}
}