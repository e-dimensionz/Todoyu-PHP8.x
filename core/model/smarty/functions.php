<?php
/*
    * Hardcoded functions file for direct call using fn($a,$b,$etc) syntax from smarty tempolates

    * Smarty plugin
    * -------------------------------------------------------------
    * File:     functions.php
    * Type:     function
    * Name:     allowed
    * Purpose:  check if user is allowed to do something
    * -------------------------------------------------------------
    */

function fn_allowed($ext, $right) {
	return TodoyuRightsManager::isAllowed($ext, $right); 
}

/* 
    * Smarty plugin
    * -------------------------------------------------------------
    * File:     functions.php
    * Type:     function
    * Name:     label
    * Purpose:  get label
    * -------------------------------------------------------------
    */
function fn_label($text, $locale = null) {
	return TodoyuLabelManager::getLabel($text, $locale);
}


/**
 * Helper function to unset array values, needed for non-referenceable parameters
 *
 * @package		Todoyu
 * @subpackage	Template
 *
 * @param	array			$params		params
 * @return	string
 */
function smarty_function_unsetArrayValue($params) {
	foreach($params['array'] as $itemKey => $itemValue) {
		if( $itemValue == $params['deletionValue'] ) {
			unset($params['array'][$itemKey]);
		}
	}

	return $params['array'];
}

/**
 * Helper function to unset array values, needed for non-referenceable parameters
 *
 * @package		Todoyu
 * @subpackage	Template
 *
 * @param	array		$array
 * @param	mixed		$deletionValue
 * @return	array
 */
function fn_unsetArrayValue($array, $deletionValue) {
	foreach($array as $itemKey => $itemValue) {
		if( $itemValue == $deletionValue ) {
			unset($array[$itemKey]);
		}
	}

	return $array;
}

/**
 * Render duration in suiting format
 *
 * @package		Todoyu
 * @subpackage	Calendar
 *
 * @param	array			$params
 * @return	string
 */
function smarty_compiler_formatDuration($params) {
	return '<?php echo TodoyuTime::formatDuration(' . $params['seconds'] . '); ?>';
}



/**
 * Render timespan in suiting format
 *
 * @package		Todoyu
 * @subpackage	Calendar
 * @param	array			$params
 * @return	string
 */
function smarty_compiler_formatRange($params) {
	return '<?php echo TodoyuTime::formatRange(' . $params['dateStart'] . ', ' . $params['dateEnd'] . ') ?>';
}

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     modifier.ucfirst.php
 * Type:     modifier
 * Name:     ucfirst
 * Purpose:  capitalize first letter
 * -------------------------------------------------------------
 */
function smarty_modifier_ucfirst($string)
{
    return ucfirst($string);
}

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     modifier.trim.php
 * Type:     modifier
 * Name:     trim
 * Purpose:  capitalize first letter
 * -------------------------------------------------------------
 */
function smarty_modifier_trim($string)
{
    return trim($string);
}


/**
 * Smarty reverse_array modifier plugin
 *
 * Type:     modifier<br>
 * Name:     reverse_array<br>
 * Purpose:  reverse arrays
 * @author   Noel McGran 
 * @param array
 * @return array
 */
function smarty_modifier_reverse_array($array)
{
    return array_reverse($array);
}

/**
 * Smarty plugin function for Header rendering
 *
 * @package		Todoyu
 * @subpackage	Template
 *
 * @param	array			$params		params
 * @param	Smarty_Internal_template			$smarty		Smarty object
 * @return	string
 */

/**
 * Substitute registered linkable elements in given text by their respective hyperlinks
 *
 * @package		Todoyu
 * @subpackage	Template
 *
 * @param	array	$params
 * @param	Smarty_Internal_template			$smarty
 * @return	string
 */
function smarty_compiler_substituteLinkableElements($params, $smarty) {
	return '<?php echo TodoyuString::substituteLinkableElements('.$params['text'].'); ?>';
}

/*
    * Smarty plugin
    * -------------------------------------------------------------
    * File:     modifier.select.php
    * Type:     modifier
    * Name:     select
    * Purpose:  Create a select box
    * -------------------------------------------------------------
    */

    function smarty_function_select($params, $smarty){
        $tmpl	= 'core/view/select.tmpl';
        $params['size'] = $params['size'] ?? 0;
        $data	= array(
            'htmlId'		=> $params['id'] ?? '',
            'htmlName'		=> $params['name'] ?? '',
            'class'			=> $params['class'] ?? '',
            'size'			=> $params['size'] == 0 ? sizeof($params['options'] ?? []) : $params['size'],
            'multiple'		=> $params['multiple'] ?? false,
            'disabled'		=> $params['disabled'] ?? false,
            'onchange'		=> $params['onchange'] ?? '',
            'onclick'		=> $params['onclick'] ?? '',
            'value'			=> $params['value'] ?? array(),
            'options'		=> $params['options'] ?? array(),
            'noPleaseSelect'=> $params['noPleaseSelect'] ?? false
        );
    
            // Append brackets to ensure multiple values are submitted
        if( $params['multiple'] ?? false ) {
            if( $data['htmlName'] !== '' && substr($data['htmlName'], -2) !== '[]' ) {
                $data['htmlName'] .= '[]';
            }
        }
    
        return Todoyu::render($tmpl, $data);
    }

    /**
 * Returns a wrapped label tag of a mail receiver, evoking person-info tooltip on rollOver
 *
 * @param	array			$params
 * @param	Smarty_Internal_Template			$smarty
 * @return	string
 */
function smarty_function_mailreceiverLabel($params, $smnarty) {
	$label = TodoyuMailReceiverManager::getMailReceiver($params['tuple'])->getLabel();

    if(empty($params['encode'])) $params['encode'] = false;
	return $params['encode'] ? htmlspecialchars($label, ENT_QUOTES, 'UTF-8', false) : $label;
}

/**
 * Smarty plugin function for label translation with dynamic values
 *
 * @package		Todoyu
 * @subpackage	Template
 *
 */
function smarty_function_LabelFormat($params, array $values, $locale = null) {
    if(empty($params['values'])) $params['values'] = [];
    else $params['values'] = [$params['values']];

	return TodoyuLabelManager::getFormatLabel($params['key'], $params['values'], $params['locale'] ?? null);
}

/**
 * Smarty plugin function for Header rendering
 *
 * @package		Todoyu
 * @subpackage	Template
 *
 * @param	array			$params		params
 * @param	Smarty_Internal_template			$smarty		Smarty object
 * @return	string
 */
function smarty_function_Header($params, $smarty) {
	$tmpl	= 'core/view/headerLine.tmpl';
	$data	= array(
		'title'		=> Todoyu::Label($params['title']),
		'class'		=> $params['class'] ?? '',
		'encode'	=> $params['encode'] ?? ''
	);

	return Todoyu::render($tmpl, $data);
}

/**
 * Smarty plugin function for Button rendering
 *
 * @package		Todoyu
 * @subpackage	Template
 *
 * @param	array			$params		params
 * @param	Smarty_Internal_template			$smarty		Smarty object
 * @return	string
 */
function smarty_function_Button($params) {
	$tmpl	= 'core/view/button.tmpl';
    $params['disabled'] = $params['disabled'] ?? false;
    $params['disable'] = $params['disable'] ?? false;

    $data	= array(
		'label'		=> $params['label'],
		'onclick'	=> $params['onclick'] ?? '',
		'class'		=> $params['class'] ?? '',
		'id'		=> $params['id'] ?? '',
		'title'		=> $params['title'] ?? '',
		'type'		=> $params['type'] ?? '',
		'disable'	=> $params['disable'] ? true : false,
		'disabled'	=> $params['disabled'] ? true : false,
		'style'		=> $params['style'] ?? ''
	);

	return Todoyu::render($tmpl, $data);
}

/**
 * Smarty plugin to restrict access to template parts
 *
 * @example
 * {restrict ext='extkey' key='rightskey'}Restricted parts{else}Unrestricted{/restrict}
 *
 * @package		Todoyu
 * @subpackage	Template
 * 
 * @param array                    $params   parameters
 * @param string                   $content  contents of the block
 * @param Smarty_Internal_Template $template template object
 * @param boolean                  &$repeat  repeat flag
 *
 * @return string content re-formatted
 * @author Monte Ohrt <monte at ohrt dot com>
 * @throws \SmartyException
 */
function smarty_block_restrict($params, $content, Smarty_Internal_Template $template, &$repeat) {
    
    if( TodoyuRightsManager::isAllowed($params['ext'], $params['right']) ) {
        return $content;
    }

    if (isset($params['hasElse'])) {
        return $params['hasElse'];
    }

    return '';
}


/**
 * Smarty plugin to restrict access to template parts to Admin
 *
 * @example
 * {restrictAdmin}Restricted parts{else}Unrestricted{/restrictAdmin}
 *
 * @package		Todoyu
 * @subpackage	Template
 * 
 * @param array                    $params   parameters
 * @param string                   $content  contents of the block
 * @param Smarty_Internal_Template $template template object
 * @param boolean                  &$repeat  repeat flag
 *
 * @return string content re-formatted
 */
function smarty_block_restrictAdmin($params, $content, Smarty_Internal_Template $template, &$repeat) {
    
    if( TodoyuAuth::isAdmin()) {
        return $content;
    }

    if (isset($params['hasElse'])) {
        return $params['hasElse'];
    }

    return '';
}

/**
 * Smarty plugin to restrict access to template parts
 *
 * @example
 * {restrictOrOwn ext='extkey' right='rightskey' idPerson='id'}Restricted parts{else}Unrestricted{/restrictOrOwn}
 *
 * @package		Todoyu
 * @subpackage	Template
 * 
 * @param array                    $params   parameters
 * @param string                   $content  contents of the block
 * @param Smarty_Internal_Template $template template object
 * @param boolean                  &$repeat  repeat flag
 *
 * @return string content re-formatted
 */
function smarty_block_restrictOrOwn($params, $content, Smarty_Internal_Template $template, &$repeat) {
    
    if( Todoyu::personid() == $params['idPerson'] || TodoyuRightsManager::isAllowed( $params['ext'] , $params['right'] ) ) {
        return $content;
    }

    if (isset($params['hasElse'])) {
        return $params['hasElse'];
    }

    return '';
}

/**
 * Smarty plugin to restrict access to template parts
 *
 * @example
 * {restrictIfNone ext='extkey' key='rightskey'}Restricted parts{else}Unrestricted{/restrictIfNone}
 *
 * @package		Todoyu
 * @subpackage	Template
 * 
 * @param array                    $params   parameters
 * @param string                   $content  contents of the block
 * @param Smarty_Internal_Template $template template object
 * @param boolean                  &$repeat  repeat flag
 *
 * @return string content re-formatted
 */
function smarty_block_restrictIfNone($params, $content, Smarty_Internal_Template $template, &$repeat) {
    
    if( Todoyu::allowedAny( $params['ext'] , $params['right'] ) ) {
        return $content;
    }

    if (isset($params['hasElse'])) {
        return $params['hasElse'];
    }

    return '';
}


/**
 * Smarty plugin to restrict access to template parts
 *
 * @example
 * {restrictInternal}Restricted parts{else}Unrestricted{/restrictInternal}
 *
 * @package		Todoyu
 * @subpackage	Template
 * 
 * @param array                    $params   parameters
 * @param string                   $content  contents of the block
 * @param Smarty_Internal_Template $template template object
 * @param boolean                  &$repeat  repeat flag
 *
 * @return string content re-formatted
 */
function smarty_block_restrictInternal($params, $content, Smarty_Internal_Template $template, &$repeat) {
    
    if( Todoyu::person()->isInternal() ) {
        return $content;
    }

    if (isset($params['hasElse'])) {
        return $params['hasElse'];
    }

    return '';
}

/**
 * Smarty plugin to restrict access to template parts
 *
 * 
 * @return string
 * @author Monte Ohrt <monte at ohrt dot com>
 * @throws \SmartyException
 */
function smarty_block_template($params, $content) {
    
    return "<?php 
    function display_template_".$params['name']."(){ 
        ob_start();
        ?> 
        
        ".$content." 

        <?php echo ob_get_clean();
    } ?>";
}

/**
 * Smarty plugin function for Header rendering
 *
 * @package		Todoyu
 * @subpackage	Template
 *
 * @param	array			$params		params
 * @return	string
 */
function smarty_compiler_contentMessage($params) {
	return '<?php Todoyu::render(\'core/view/contentMessage.tmpl\', array(\'labels\'=>explode(\'|\', ' . ($params['label']  ?? "''"). '),\'class\'=>' . ($params['class'] ?? "''") . ',\'content\'=>' . ($params['content'] ?? "''") . '), false, true); ?>';
}


/**
 * Smarty plugin function for Header rendering
 *
 * @package		Todoyu
 * @subpackage	Template
 *
 * @param	array			$params		params
 * @param	Smarty_Internal_template			$smarty		Smarty object
 * @return	string
 */
function smarty_compiler_tpl_output($params) {
	return '<?php echo smarty_block_template_'.$params['name'].'(); ?>';
}

/**
 * Format an integer to hours:minutes
 *
 * @package		Todoyu
 * @subpackage	Template
 *
 * @param	array  	$params
 * @param	Smarty_Internal_Template			$smarty
 * @return	string
 */
function smarty_compiler_HourMin($params, $smarty) {
	return '<?php echo TodoyuTime::formatTime(' . $params['seconds'] . '); ?>';
}

/**
 * Format an integer to hours:minutes:seconds
 *
 * @package		Todoyu
 * @subpackage	Template
 *
 * @param	array  	$params
 * @param	Smarty_Internal_Template			$smarty
 * @return	string
 */
function smarty_compiler_HourMinSec($params, $smarty) {
	return '<?php echo TodoyuTime::formatTime(' . $params['seconds'] . ', true); ?>';
}

/**
 * Check in template if user is logged in
 *
 * @package		Todoyu
 * @subpackage	Template
 *
 * @param	Smarty_Internal_Template		$smarty
 * @return	string
 */
function smarty_compiler_isLoggedIn($smarty) {
	return '<?php TodoyuAuth::isLoggedIn() ?>';
}

/**
 * Smarty plugin function for label translation
 *
 * @package		Todoyu
 * @subpackage	Template
 *
 * @param	array			$params		params
 * @param	Smarty_Internal_template			$smarty		Smarty object
 * @return	string
 */
function smarty_compiler_Label($params, $smarty) {
	return '<?php echo TodoyuLabelManager::getLabel(' . $params['text'] . ', ' . ($params['locale'] ?? "null"). '); ?>';
}


/**
 * Format the amount of hours
 *
 * @package		Todoyu
 * @subpackage	Template
 *
 * @param	array			$params		params
 * @return	string
 */
function smarty_compiler_formatHours($params) {
	return '<?php echo TodoyuTime::formatHours(' . $params['workload'] . ', ' . ($params['leadingZero'] ?? false). '); ?>';
}



/**
 * Get name of country
 *
 * @package		Todoyu
 * @subpackage	Template
 *
 * @param	array			$params		params
 * @return	string
 */
function smarty_function_countryName($params) {
	$idCountry	= (int) $params['id'];

	if( $idCountry > 0 ) {
		$country	= TodoyuStaticRecords::getCountry($idCountry);

		return TodoyuStaticRecords::getLabel('country', $country['iso_alpha3']);
	} else {
		return '';
	}
}


/**
 * Render balloon info
 *
 * @param	array			$params		params
 * @return	string
 */
function smarty_compiler_infoBalloon($params) {
	return '<?php Todoyu::render(\'core/view/infoballoon.tmpl\', array(\'label\'=> ' . $params['label'] . ',\'id\'=>' . ($params['id'] ?? '') . ',\'content\'=>' . ($params['content'] ?? '') . ',\'balloonWidth\'=>' . ($params['balloonWidth'] ?? 0) . '),  false, true) ?>';
}



/**
 * Plugin for listing renderer
 *
 * @param	array			$params		params
 * @return	string
 */
function smarty_function_List($params) {
	return TodoyuListingRenderer::render($params['ext'], $params['list'], $params['offset'] ?? 0, $params['noPaging'] ?? false, $params['params'] = []);
}


/**
 * Render CSS classnames from boolean attribute names+values of given record. E.g. is_preferred => isPreferred0 / isPreferred1
 *
 * @param	array			$params		params
 * @return	string
 */
function smarty_function_getRecordBooleanColumnsClassnames($params) {
	$classNames = '';

	foreach($params['record'] as $columnKey => $columnValue) {
			// Boolean field column containing TRUE
		if( substr($columnKey, 0, 3) === 'is_' ) {
			$classParts = explode('_', $columnKey);
			foreach($classParts as $index => $part) {
				$classNames .= ($index > 0 ? ucfirst($part) : strtolower($part));
			}
			$classNames .= $columnValue . ' ';
		}
	}

	return trim($classNames);
}




/**
 * Build timerange selector HTML code
 *
 * @package		Todoyu
 * @subpackage	Template
 *
 * @param	array			$params		params
 * @return	string
 */
function smarty_compiler_timerange($params, $id, $name, $range = array(), $nameWrap = '') {
	return '<?php echo TodoyuRenderer::renderTimerange(' . $params['id'] . ', ' . $params['name'] . ', ' .( $params['range'] ?? []) . ', ' . ($params['nameWrap'] ?? '') . '); ?>';
}


/**
 * Replace spaces with &nbsp; entities
 * Prevent line breaks on spaces
 *
 * @package		Todoyu
 * @subpackage	Template
 *
 * @param	array			$params		params
 * @return	string
 */
function smarty_compiler_nobreak($params) {
	return '<?php echo str_replace(\' \', \'&nbsp;\', ' . $params['text'] . '); ?>';
}


/**
 * Replace line breaks "\n" with ODT style line breaks
 *
 * @package		Todoyu
 * @subpackage	Template
 *
 * @param	array			$params		params
 * @return	string
 */
function smarty_compiler_OdtLinebreaks($params) {
	return '<?php echo str_replace("\n", \'<text:line-break/>\', ' . $params['text'] . '); ?>';
}




/**
 * Render select element with grouped options
 *
 * @package		Todoyu
 * @subpackage	Template
 *
 * @param	array			$params		params
 * @return	string
 */
function smarty_function_selectGrouped($params) {
	$data	= array(
		'id'			=> $params['id'] ?? '',
		'name'			=> $params['name'] ?? '',
		'class'			=> $params['class'] ?? '',
		'size'			=> $params['size'] ?? 0,
		'multiple'		=> $params['multiple'] ?? false,
		'disabled'		=> $params['disabled'] ?? false,
		'onchange'		=> $params['onchange'] ?? '',
		'onclick'		=> $params['onclick'] ?? '',
		'value'			=> $params['value'] ?? '',
		'options'		=> $params['options'] ?? '',
		'noPleaseSelect'=> $params['noPleaseSelect'] ?? false
	);

	return TodoyuRenderer::renderSelectGrouped($data);
}


/**
 * Convert HTML code to text, keep as much format as possible
 *
 * @package		Todoyu
 * @subpackage	Template
 *
 * @param	array			$params		params
 * @return	string
 */
function smarty_compiler_html2text($params) {
	return '<?php echo TodoyuString::html2text(' . $params['html'] . ', ' . ($params['decodeEntity'] ?? false) . '); ?>';
}


/**
 * Get address label
 *
 * @param	string			$html		html
 * @param	boolean			$decodeEntity	decode entity
 * @return	string
 */
function fn_html2text($html, $decodeEntity = false) {
	return TodoyuString::html2text($html, $decodeEntity);;
}



/**
 * Include given file's content with special- or all applicable characters converted to HTML character entities
 *
 * @package		Todoyu
 * @subpackage	Template
 *
 * @param	array			$params		params
 * @param	Smarty_Internal_template			$smarty		Smarty object
 * @return	string
 */
function smarty_function_includeEscaped($params, $smarty) {
	require_once( __DIR__ . '/include.php' );

	$content	= smarty_tpl_include($smarty, $params['file']);

	return ($params['convertSpecialCharsOnly'] ?? true) ? htmlspecialchars($content, ENT_QUOTES, 'UTF-8', false) : htmlentities($content, ENT_QUOTES, 'UTF-8', false);
}




/**
 * Check if a value (or a list of) exists in an array
 *
 * @package		Todoyu
 * @subpackage	Template
 *
 * @param	array			$params		params
 * @return	string
 */
function smarty_function_inArray($params) {
	if( ! is_array($params['value'] ?? null) ) {
		$value	= explode(',', $params['value']);
	}
	if( ! is_array($params['array'] ?? null) ) {
		$array	= explode(',', $params['array']);
	}

	$mix	= array_intersect($value, $array);

	return sizeof($mix) > 0 ;
}



/**
 * Encode quotes to not interfere with html attribute quote parting
 * " => \042
 * ' => \047
 *
 * @package		Todoyu
 * @subpackage	Template
 *
 * @param	array			$params		params
 * @return	string
 */
function smarty_compiler_escapeQuotesForHtmlAttributes($params) {
	return '<?php echo TodoyuString::escapeQuotesForHtmlAttributes(' . $params['string'] . '); ?>';
}


/**
 * Get formatted file size
 *
 * @package		Todoyu
 * @subpackage	Template
 *
 * @param	array			$params		params
 * @return	string
 */
function smarty_compiler_filesize($params) {
	return '<?php echo TodoyuString::formatSize(' . $params['bytes'] . '); ?>';
}

/**
 * Render numeric value with (at least) two digits
 *
 * @package		Todoyu
 * @subpackage	Template
 *
 * @param	array			$params		params
 * @return	string
 */
function smarty_compiler_twoDigits($params) {
	return '<?php echo sprintf(\'%02d\', ' . $params['value'] . '); ?>';
}



/**
 * Debug some variable inside a Smarty template
 *
 * @package		Todoyu
 * @subpackage	Template
 *
 * @param	array			$params		params
 * @return	string
 */
function smarty_function_debug($params) {
	if ( $params['phpFormat'] ?? false ) {
			// Use PHP syntax formatting
		TodoyuDebug::printPHP($params['variable']);

		return '';
	} else {
			// Simple print_r
		return '<pre style="z-index:200; background-color:#fff;">' . print_r($params['variable'], true) . '</pre>';
	}
}


/**
 * Subtract given subtrahend from given minuend
 *
 * @package		Todoyu
 * @subpackage	Template
 *
 * @param	array			$params		params
 * @return	string
 */
function smarty_compiler_subtract($params) {
	return '(floatval(' . $params['minuend'] . ')-floatval(' . $params['subtrahend'] . '))';
}


/**
 * Check if user has right and given user ID is the current users ID
 * Get function string to check this
 *
 * @package		Todoyu
 * @subpackage	Template
 *
 * @param	array			$params		params
 * @return	string
 */
function smarty_compiler_allowedAndOwn($params) {
	return '<?php TodoyuRightsManager::isAllowed(' . $params['ext'] . ',' . $params['right'] . ') && Todoyu::personid()==' . $params['idPerson'] . ' ?>';
}

/**
 * Check whether user has right, or given user ID is the current users ID. Get function string to check this
 *
 * @package		Todoyu
 * @subpackage	Template
 *
 * @param	array			$params		params
 * @return	string
 */
function smarty_compiler_allowedOrOwn($params) {
	return '<?php TodoyuRightsManager::isAllowed(' . $params['ext'] . ',' . $params['right'] . ') || Todoyu::personid()==' . $params['idPerson']. ' ?>';
}



/**
 * Check whether any of the given rights are allowed. Get function string to check this
 *
 * @package		Todoyu
 * @subpackage	Template
 *
 * @param	array			$params		params
 * @return	string
 */
function smarty_compiler_allowedAny($params) {
	return '<?php Todoyu::allowedAny(' . $params['ext'] . ',' . $params['rightsList'] . ') ?>';
}



/**
 * Check if all given rights are allowed. Get function string to check this
 *
 * @package		Todoyu
 * @subpackage	Template
 *
 * @param	array			$params		params
 * @return	string
 */
function smarty_compiler_allowedAll($params) {
	return '<?php Todoyu::allowedAll(' . $params['ext'] . ',' . $params['rightsList'] . ') ?>';
}

/**
 * Check whether right is given. Get function string to check this
 *
 * @package		Todoyu
 * @subpackage	Template
 *
 * @param	array			$params		params
 * @return	string
 */
function smarty_compiler_allowed($params) {
	return '<?php TodoyuRightsManager::isAllowed(' . $params['ext'] . ',' . $params['right'] . ') ?>';
}


/**
 * Build page content title
 *
 * @package		Todoyu
 * @subpackage	Template
 *
 * @param	array			$params		params
 * @return	string
 */
function smarty_compiler_Title($params) {
	return '\'<h5>\' . htmlentities(Todoyu::Label(' . $params['title'] . '), ENT_QUOTES, \'UTF-8\', false) . \'</h5>\'';
}

/**
 * Substitute URLs by hyperlinks
 *
 * @package		Todoyu
 * @subpackage	Template
 *
 * @param	array			$params		params
 * @return	string
 */
function smarty_compiler_linkUrls($params) {
	return '<?php echo TodoyuString::replaceUrlWithLink(' . $params['text'] . ') ?>';
}


/**
 * Clean bad tags from HTML code
 *
 * @package		Todoyu
 * @subpackage	Template
 *
 * @param	array			$params		params
 * @return	string
 */
function smarty_compiler_cleanHtml($params) {
	return '<?php echo TodoyuHtmlFilter::clean(' . $params['html'] . '); ?>';
}


/**
 * Format an SQL datetime string as date format of current locale
 *
 * @package		Todoyu
 * @subpackage	Template
 *
 * @param	array			$params		params
 * @return	string
 */
function smarty_compiler_formatSqlDate($params) {
	return '<?php echo TodoyuTime::formatSqlDate(' . $params['date'] . ', "' . ($params['format'] ?? 'date') . '"); ?>';
}


/**
 * Special Todoyu date format. Format a date based on registered key in the core
 *
 * @package		Todoyu
 * @subpackage	Template
 *
 * @param	array			$params		params
 * @return	string
 */
function smarty_compiler_dateFormat($params) {
	return '<?php echo TodoyuTime::format(' . $params['timestamp'] . ', ' . $params['formatName'] . '); ?>';
}


/**
 * Special Todoyu time format. Format time like 23:59 (or 23:59:59)
 *
 * @package		Todoyu
 * @subpackage	Template
 *
 * @param	array			$params		params
 * @return	string
 */
function smarty_compiler_timeFormat($params) {
	return 'TodoyuTime::formatTime(' . $params['timestamp'] . ', ' . ($params['withSeconds'] ?? false) . ', ' . ($params['round'] ?? true) . ')';
}

/**
 * View some variable (from inside a Smarty template) in firebug
 *
 * @package		Todoyu
 * @subpackage	Template
 *
 * @param	array			$params		params
 * 
 */
function smarty_function_firebug($params) {
	TodoyuDebug::printInFirebug($params['variable'], $params['label'] ?? 'smarty debug');
}

function smarty_modifier_sizeof($array){
    return empty($array) ? 0 : sizeof($array);
}

function smarty_modifier_htmlencode($string){
    return htmlspecialchars_decode($string);
}