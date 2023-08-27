<?php
/**
 * Check whether the given asset is an image type that GD lib can handle
 *
 * @package		Todoyu
 * @subpackage	Template
 *
 * @param	int		$id
 * @return	boolean
 */
function fn_isAssetGDcompatibleImage($id) {
	return TodoyuAssetsImageResizer::isGDcompatibleImage($id);
}

/**
 * Check right of current person to see given asset
 *
 * @package		Todoyu
 * @subpackage	Template
 *
 * @param	integer		$idAsset
 * @return	boolean
 */
function fn_isAssetSeeAllowed($idAsset) {
	return TodoyuAssetsRights::isSeeAllowed($idAsset);
}



/**
 * Check right of current person to delete given asset
 *
 * @package		Todoyu
 * @subpackage	Template
 *
 * @param	integer		$idAsset
 * @return	boolean
 */
function fn_isAssetDeleteAllowed($idAsset) {
	return TodoyuAssetsRights::isDeleteAllowed($idAsset);
}
