<?php 

/**
 * Extract sum of hours of calendar week from given daytracks data
 *
 * @param	array			$params
 * @return	string
 */
function smarty_function_sumTrackedCW($params) {
	$sum	= 0;
	$cw		= date('W', $params['timestamp']);
	foreach($params['tracks'] as $timestamp => $daytracks) {
		if( date('W', $timestamp) === $cw ) {
			foreach($daytracks as $tracks) {
				if( is_array($tracks) > 0 ) foreach($tracks as $track) {
					$sum	+= $track['workload_tracked'];
				}
			}
		}
	}

	return TodoyuTime::formatHours($sum);
}



/**
 * Remove enclosing wrap from given string
 *
 * @param	array			$params
 * @return	string
 */
function smarty_function_unwrap($params) {
	if(TodoyuString::startsWith($params['str'], $params['wrap'] ?? '') && TodoyuString::endsWith($params['str'], $params['wrap'] ?? '')) {
		$wrapLen	= strlen($params['wrap'] ?? '');
		$str = substr($params['str'], $wrapLen, strlen($params['str']) - $wrapLen * 2);
	}

	return $str;
}
