<?php

namespace Application\Model\Databases;

use Xerxes\Utility\DataValue,
	Xerxes\Utility\Parser;

/**
 * Metalib SubCategory
 *
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license 
 * @package Xerxes
 */

class Subcategory extends DataValue
{
	public $subcategory_id;
	public $name;
	public $sequence;
	public $category_id;
	public $databases = array();
}