<?php 
function smarty_tpl_include($smarty, $file, $cache_time = null, $cache_id = null, $compile_id = null, $data = '_root', $assign = null, array $rest = array())
{
	if ($file === '') {
		return;
	}

	if (preg_match('#^([a-z]{2,}):(.*)$#i', $file, $m)) {
		// resource:identifier given, extract them
		$resource = $m[1];
		$identifier = $m[2];
	} else {
		// get the current template's resource
		$resource = $smarty->getTemplate()->getResourceName();
		$identifier = $file;
	}

	try {
		if (!is_numeric($cache_time)) {
			$cache_time = null;
		}
		$include = $smarty->templateFactory($resource, $identifier, $cache_time, $cache_id, $compile_id);
	} catch (Exception $e) {
		throw new Exception('Include : Security restriction : '.$e->getMessage(), E_USER_WARNING);
	} catch (Exception $e) {
		throw new Exception('Include : '.$e->getMessage(), E_USER_WARNING);
	}

	if ($include === null) {
		throw new Exception('Include : Resource "'.$resource.':'.$identifier.'" not found.', E_USER_WARNING);
	} elseif ($include === false) {
		throw new Exception('Include : Resource "'.$resource.'" does not support includes.', E_USER_WARNING);
	}

	if (is_array($data)) {
		$vars = $data;
	} elseif (is_array($cache_time)) {
		$vars = $cache_time;
	} else {
		$vars = $smarty->readVar($data);
	}

	if (count($rest)) {
		$vars = $rest + $vars;
	}

	$out = $smarty->get($include, $vars);

	if ($assign !== null) {
		$smarty->assignInScope($out, $assign);
	} else {
		return $out;
	}
}