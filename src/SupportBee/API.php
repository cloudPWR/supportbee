<?php

namespace SupportBee;

use \InvalidArgumentException;
use \Requests;
use SupportBee\Exceptions\HTTPException as HTTPException;

/**
 * Class API
 *
 * @package SupportBee\API
 */
class API {

	protected static $http_error_msgs = array(
		401 => 'There was an error authenticating with the token.',
		403 => 'There was an error authenticating with the token.'
	);

	protected function validate( $options = array(), $valid = array(), $required = array() )
	{	
		if ( !is_array($options) )
			throw new InvalidArgumentException( 'Parameters need to be passed as array' );

		$options = array_keys( $options );

		foreach( $options as $k => $v)
		{
			if ( !in_array( $v, $valid) )
				throw new InvalidArgumentException( 'Not a valid parameter passed' );
		}

		if ( count( $required ) && !array_intersect( $options, $required))
		throw new InvalidArgumentException( 'Required parameter not passed' );
	}

	protected function tfTostring(&$value,&$key) 
	{
		if ($value === true) 
		{
			$value = 'true';
		}
		else if ($value === false) 
		{
			$value = 'false';
		}
	}

	protected function inject( &$options )
	{
		$options = array_merge( $options, array(
			'auth_token' => SupportBee::$auth_token		
		));
	}
	
	private function request( $path, $options )
	{
		return Requests::get(SupportBee::$base_url.$path.'?'.http_build_query( $options ), SupportBee::$headers, $options);
	}
	
	protected function handle_response( $resp )
	{
		if( $resp->status_code != 200 )
			throw new HTTPException( ( isset( self::$http_error_msgs[$resp->status_code] ) ) ? self::$http_error_msgs[$resp->status_code] : 'An HTTP error with status code '.$resp->status_code.' occured' );
		else
			return json_decode($resp->body, true);
	}

	protected function process_request( $path, $options = array() )
	{
		self::inject( $options );
		array_walk($options,'self::tfTostring');

		$resp = self::request( $path, $options );
		return self::handle_response( $resp );		
	}
}