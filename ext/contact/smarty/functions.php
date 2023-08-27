<?php 
/**
 * Ext specific Smarty functions
 *
 * @package		Todoyu
 * @subpackage	Template
 */
function fn_isCompanySeeAllowed($idCompany) {
	return TodoyuContactCompanyRights::isSeeAllowed($idCompany);
}

/**
 * Get person ID
 *
 * @package		Todoyu
 * @subpackage	Template
 *
 * @param	Smarty_Internal_Template		$smarty
 * @return	string
 */
function smarty_function_personid($params, $smarty) {
	return Todoyu::personid();
}


/**
 * Get person label
 *
 * @package		Todoyu
 * @subpackage	Template
 *
 * @param	array		$params
 * @param	Smarty_Internal_Template		$smarty
 * @return	string
 */
function smarty_function_personLabel($params, $smarty) {
	if( !TodoyuContactPersonRights::isSeeAllowed($params['id']) ) {
		return '';
	}

	$htmlID		= ($params['prefix'] ?? 'person') . '-' . ($params['idRecord'] ?? 0) . '-' . $params['id'];
	$personLabel= TodoyuContactPersonManager::getLabel($params['id']);
	$attributes	= array(
		'id'	=> $htmlID,
		'class'	=> trim('quickInfoPerson ' . ($params['class'] ?? ''))
	);

	$personTag		= TodoyuString::buildHtmlTag($params['tag'] ?? 'span', $attributes, $personLabel);
	$quickInfoScript= TodoyuString::wrapScript('Todoyu.Ext.contact.QuickInfoPerson.add(\'' . $htmlID . '\');');

	return $personTag . $quickInfoScript;
}

/**
 * Check whether given ID belongs to the current person
 *
 * @package		Todoyu
 * @subpackage	Template
 *
 * @param	integer			$idPerson
 * @return	boolean
 */
function fn_isPersonID($idPerson) {
	return Todoyu::personid() === intval( $idPerson );
}



/**
 * Get person ID
 *
 * @package		Todoyu
 * @subpackage	Template
 *
  * @return	string
 */
function smarty_compiler_personid() {
	return '<?php echo Todoyu::personid(); ?>';
}



/**
 * Get the name to given person ID
 *
 * @package		Todoyu
 * @subpackage	Template
 *
 * @param	array			$params
 * @return	string
 */
function smarty_compiler_name($params) {
	return '<?php echo TodoyuContactPersonManager::getPerson(' . $params['id'] . ')->getFullName(true); ?>';
}


/**
 * Get address label
 *
 * @param	array				$params
 * @return	string
 */
function smarty_compiler_addressLabel($params) {
	return '<?php echo TodoyuContactAddressManager::getLabel(' . $params['id'] . '); ?>';
}



/**
 * Get person shortname, optionally generate it from first- and lastname
 *
 * @package		Todoyu
 * @subpackage	Template
 *
 * @param	array		$params
 * @return	string
 */
function smarty_function_shortname($params) {
	$idPerson	= intval($params['idPerson'] ?? 0);
	$person	= TodoyuContactPersonManager::getPerson($idPerson);

	$shortname	= $person->getShortname();
	if( empty($shortname) && $params['truncateFromFullnameIfMissing'] ?? false ) {
		$shortname	= strtoupper(substr($person->getFirstName(), 0, 2) . substr($person->getLastName(), 0, 2));
	}

	return $shortname;
}



/**
 * Get name of contact info type
 *
 * @package		Todoyu
 * @subpackage	Template
 *
 * @param	array		$params
 * @return	string
 */
function smarty_function_labelContactinfotype($params) {
	$idContactinfotype = intval($params['idContactinfotype']);

	return TodoyuContactContactInfoManager::getContactInfoTypeName($idContactinfotype);
}



/**
 * Returns the label of the address type with given ID
 *
 * @param	array		$params
 * @return	string
 */
function smarty_compiler_addressType($params) {
	return '<?php echo TodoyuContactAddressManager::getAddresstypeLabel(' . $params['idAddressType'] . '); ?>';
}



/**
 * Returns the salutation Label of a person
 *
 * @param	array		$params
 * @return	string
 */
function smarty_function_salutationLabel($params) {
	$idPerson	= intval($params['idPerson']);

	if( $idPerson > 0 ) {
		return TodoyuContactPersonManager::getPerson($idPerson)->getSalutationLabel();
	}

	return '';
}



/**
 * Renders the image of given person
 *
 * @param	array		$params
 * @return	string
 */
function smarty_compiler_personImage($params) {
	return '<?php echo TodoyuContactImageManager::getContactImage(' . $params['idPerson'] . ', \'person\'); ?>';
}



/**
 * Renders the image of given person
 *
 * @param	array		$params
 * @return	string
 */
function smarty_function_personAvatar($params) {
	return TodoyuContactImageManager::getAvatarImage($params['id'], 'person');
}



/**
 * Renders image of given company
 *
 * @param	array		$params
 * @return	string
 */
function smarty_compiler_companyImage($params) {
	return '<?php echo TodoyuContactImageManager::getContactImage(' . $params['id'] . ', \'company\'); ?>';
}



/**
 * Checks if current person is allowed to edit given company
 *
 * @param	array		$params
 * @return	boolean
 */
function fn_isCompanyEditAllowed($id) {
	return TodoyuContactCompanyRights::isEditAllowed(' . $id . ');
}


/**
 * Checks if current person is allowed to delete given company
 *
 * @param	integer			$idCompany
 * @return	boolean
 */
function fn_isCompanyDeleteAllowed($idCompany) {
	return TodoyuContactCompanyRights::isDeleteAllowed($idCompany);
}



/**
 * Checks if current person is allowed to edit given persons
 *
 * @param	integer			$idPerson
 * @return	boolean
 */
function fn_isPersonEditAllowed($idPerson) {
	return TodoyuContactPersonRights::isEditAllowed($idPerson);
}



/**
 * Checks if current person is allowed to see given person
 *
 * @param	integer			$idPerson
 * @return	boolean
 */
function fn_isPersonSeeAllowed($idPerson) {
	return TodoyuContactPersonRights::isSeeAllowed($idPerson);
}



/**
 * Checks if current person is allowed to delete given person
 *
 * @param	integer				$idPerson
 * @return	boolean
 */
function fn_isPersonDeleteAllowed($idPerson) {
	return TodoyuContactPersonRights::isDeleteAllowed($idPerson);
}



/**
 * Checks if current Person is internal
 *
 * @return	boolean
 */
function fn_isInternal() {
	return TodoyuAuth::isInternal();
}



/**
 * Checks if current person has access to the addresstype of current record (company / person)
 *
 * @param	string		$type
 * @param	integer		$idRecord
 * @param	integer		$idAddressType
 * @return	boolean
 */
function fn_isAddressTypeSeeAllowed($type, $idRecord, $idAddressType) {
	$idRecord		= intval($idRecord);
	$idAddressType	= intval($idAddressType);

	if( $type === 'person' ) {
		return TodoyuContactRights::isAddresstypeOfPersonSeeAllowed($idRecord, $idAddressType);
	} else if( $type === 'company' ) {
		return TodoyuContactRights::isAddresstypeOfCompanySeeAllowed($idRecord, $idAddressType);
	}

	return false;
}



/**
 * Checks if current person has access to the contactinfotype of current record (company / person)
 *
 * @param	string		$type
 * @param	integer		$idRecord
 * @param	integer		$idAddressType
 * @return	boolean
 */
function fn_isContactinfotypeSeeAllowed($type, $idRecord, $idAddressType) {
	$idRecord		= intval($idRecord);
	$idAddressType	= intval($idAddressType);

	if( $type === 'person' ) {
		return TodoyuContactRights::isContactinfotypeOfPersonSeeAllowed($idRecord, $idAddressType);
	} else if( $type === 'company' ) {
		return TodoyuContactRights::isContactinfotypeOfCompanySeeAllowed($idRecord, $idAddressType);
	}

	return false;
}



/**
 * @param	array	$contactInfoData
 * @return	string
 */
function smarty_function_renderContactInfoType($params) {
	$contactInfoData = TodoyuHookManager::callHookDataModifier('contact', 'contactinfotype.render', $params['contactInfoData']);

	return $contactInfoData['html'];
}
