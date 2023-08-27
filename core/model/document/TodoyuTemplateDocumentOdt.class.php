<?php
/****************************************************************************
* todoyu is published under the BSD License:
* http://www.opensource.org/licenses/bsd-license.php
*
* Copyright (c) 2012, snowflake productions GmbH, Switzerland
* All rights reserved.
*
* This script is part of the todoyu project.
* The todoyu project is free software; you can redistribute it and/or modify
* it under the terms of the BSD License.
*
* This script is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the BSD License
* for more details.
*
* This copyright notice MUST APPEAR in all copies of the script.
*****************************************************************************/

/**
 * Use an openOffice writer document as template, and replace all markers
 * Smarty will process the content.xml file for Smarty variables
 *
 * @package		Todoyu
 * @subpackage	Core
 */
class TodoyuTemplateDocumentOdt extends TodoyuTemplateDocumentOpenXML {

	/**
	 * Build parsed template
	 */
	protected function build() {
			// Set content type
		$this->setContentType('application/vnd.oasis.opendocument.text');
			// Load the XML content from the template file
		$this->loadXMLContent('content.xml');
			// Prepare the XML content (move some markers)
		$this->prepareXML();
			// Create an archive again for the odt
		$this->buildArchive();
	}



	/**
	 * Prepare the XML files
	 * Move sections markers where necessary
	 */
	private function prepareXML() {
		$this->removeSoftPageBreaks();
		$this->prepareSmartyTagSpans();
		$this->prepareListXML();
		$this->prepareRowXML();
		$this->preparePhpXML();
		$this->prepareForeach();
		$this->prepareConditionXML();
		$this->removeAnnotations();
		$this->convertXmlEntitiesInSmartyTags();

//		TodoyuHeader::sendTypeXML();
//		echo $this->xmlContent;
//		exit();
	}



	/**
	 * Remove soft-page-break tags from xml
	 * They destroy all Smarty code
	 *
	 */
	private function removeSoftPageBreaks() {
		$this->xmlContent = str_replace('<text:soft-page-break/>', '', $this->xmlContent);
	}



	/**
	 * Prepare list xml sections to repeat list elements
	 */
	private function prepareListXML() {
		$patternList= '|(<text:list-item>)(.*?)\[--LI:({.*?})(.*?)({/.*?})--LI\](.*?)(</text:list-item>)|sm';
		$replaces	= array();

		preg_match_all($patternList, $this->xmlContent, $matches);

		foreach($matches[0] as $index => $listXML) {
			$replaces[$listXML] = $matches[3][$index] . $matches[1][$index] . $matches[2][$index] . $matches[4][$index] . $matches[6][$index] . $matches[7][$index] . $matches[5][$index];
		}

		$this->xmlContent = str_replace(array_keys($replaces), array_values($replaces), $this->xmlContent);
	}



	/**
	 * Prepare the XML with the row syntax
	 */
	private function prepareRowXML() {
		$markerRowStart	= '\[TR:';
		$markerRowEnd	= ':TR\]';

			// Remove text spans around the row tags
		$patternRowTagA	= '|<text:span[^>]*?>(' . $markerRowStart . ')</text:span>|s';
		$patternRowTagB	= '|<text:span[^>]*?>(' . $markerRowEnd . ')</text:span>|s';

		$this->xmlContent	= preg_replace($patternRowTagA, '\1', $this->xmlContent);
		$this->xmlContent	= preg_replace($patternRowTagB, '\1', $this->xmlContent);


			// Pattern to find all table rows
		$patternRow		= '|<table:table-row[^>]*?>.*?</table:table-row>|s';
		$replaces		= array();

			// Find row start and end tags
		$patternRowStart= '/' . $markerRowStart . '({[^}]*})/';
		$patternRowEnd	= '/({\/[^}]*})' . $markerRowEnd . '/';

			// Find all rows
		preg_match_all($patternRow, $this->xmlContent, $rowMatches);

			// Check for the row syntax in the matched row parts and modify the row
		foreach($rowMatches[0] as $rowXML) {
				// Search start tags
			preg_match_all($patternRowStart, $rowXML, $rowStartMatches);
				// Search end tags
			preg_match_all($patternRowEnd, $rowXML, $rowEndMatches);

			if( sizeof($rowStartMatches[0]) === 0 && sizeof($rowEndMatches[0]) === 0 ) {
				continue;
			}

				// Remove row markers and the smarty tag
			$newRowXML	= str_replace($rowStartMatches[0], '', $rowXML, $count);
				// Remove row markers and the smarty tag
			$newRowXML	= str_replace($rowEndMatches[0], '', $newRowXML);
				// Pre- and postfix the found markers
			$newRowXML	= implode('', $rowStartMatches[1]) . $newRowXML . implode('', $rowEndMatches[1]);

			$replaces[$rowXML] = $newRowXML;
		}

		$this->xmlContent = str_replace(array_keys($replaces), array_values($replaces), $this->xmlContent);
	}



	/**
	 * Prepare the XML for the conditions (if,else)
	 * Free them from wrapping with text nodes
	 */
	private function prepareConditionXML() {
			// Only one condition in a line: {if $xxx} or {/if}
		$pattern	= '#(<text:p[^>]*?>)\s?({[/]?(?:if|else)[^}]*?})\s?(</text:p>)#is';
		$replace	= '$2';

		$this->xmlContent = preg_replace($pattern, $replace, $this->xmlContent);

			// Conditions with content between: {$if $xxx}yyy{else}zzz{/if}
		$pattern	= '#(<text:p[^>]*?>)\s*?({if[^}]*?})(.*?)({/if})(</text:p>)#is';
		$replace	= '$2$1$3$5$4';

		$this->xmlContent = preg_replace($pattern, $replace, $this->xmlContent);
	}



	/**
	 * Prepare the XML to include PHP code
	 */
	private function preparePhpXML() {
		$replace	= array(
			'[--PHP:'	=> '<?php',
			'--PHP]'	=> '?>',
			'„'			=> '"',
			'“'			=> '"'
		);

		$this->xmlContent = str_replace(array_keys($replace), array_values($replace), $this->xmlContent);
	}



	/**
	 * Prepare the XML for Smarty tags in span
	 * Between Smarty braces, there may be <span> tags which add formatting information.
	 * Move them out of the Smarty tags
	 */
	private function prepareSmartyTagSpans() {
		$pattern	= '/{.*?}/';

		$this->xmlContent = preg_replace_callback($pattern, array($this, 'replaceStyleTagsInSmartyTags'), $this->xmlContent);
	}



	/**
	 * Callback to replace office style tags in Smarty tags
	 *
	 * @param	array	$matchingElements
	 * @return	string
	 */
	private function replaceStyleTagsInSmartyTags(array $matchingElements) {
		$smartyTag	= $matchingElements[0];

			// Replace space tags
		$smartyTag	= str_replace('<text:s/>', '', $smartyTag);

			// Pattern for open and closing tags
		$patternWrappings	= '/(<(.*?) ?[^>]*?>)(.*?)(<\/\2>)/';
		$replaceWrappings	= '\3';

		$smartyTag	= preg_replace($patternWrappings, $replaceWrappings, $smartyTag);


			// Move single opening tags to the start of the string
		$patternOpen	= '/({.*?)(<[^\/]*?:[^ ] ?[^>]*?>)(.*?})/';
		$replaceOpen	= '\2\1\3';

		$smartyTag	= preg_replace($patternOpen, $replaceOpen, $smartyTag);

			// Move single closing tags to the end of the string
		$patternClose	= '/({.*?)(<\/.*?>)(.*?})/';
		$replaceClose	= '\1\3\2';

		$smartyTag	= preg_replace($patternClose, $replaceClose, $smartyTag);

		return $smartyTag;
	}



	/**
	 * Prepare XML content for parsing of {foreach} iterations
	 */
	private function prepareForeach() {
			// Only foreach in a line
		$pattern	= '/(<text:[^>]*?>)({[\/]?foreach[^\}]*?})(<\/text:[^>]*?>)/';
		$replace	= '\2';

		$this->xmlContent	= preg_replace($pattern, $replace, $this->xmlContent);

			// Additional content in line after foreach
		$pattern	= '/(<text:p [^>]*?>)({foreach[^}]*?})(.*?)(<\/text:p>)/';
		$replace	= '\2\1\3\4';

		$this->xmlContent	= preg_replace($pattern, $replace, $this->xmlContent);
	}



	/**
	 * Remove annotations from documents
	 *
	 */
	private function removeAnnotations() {
		$pattern	= '/<office:annotation>.*?<\/office:annotation>/is';
		$replace	= '';

		$this->xmlContent	= preg_replace($pattern, $replace, $this->xmlContent);
	}



	/**
	 * Convert encoded entities back to original char when it's inside a Smarty tag
	 * Useful for < or > which are &gt; and &lt; in an XML document
	 */
	private function convertXmlEntitiesInSmartyTags() {
		$pattern	= '/{(.*?)(&([^;]+?);)(.*?)}/';

		$this->xmlContent	= preg_replace_callback($pattern, array($this,'callbackConvertEntities'), $this->xmlContent);

	}



	/**
	 * Callback to convert XML entities back inside of a Smarty tag
	 *
	 * @param	array	$match
	 * @return	string
	 */
	private static function callbackConvertEntities(array $match) {
		$entity	= html_entity_decode($match[2], ENT_QUOTES, 'UTF-8');

		return str_replace($match[2], $entity, $match[0]);
	}

}

?>