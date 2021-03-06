<?php

/*
 * This file is part of Xerxes.
 *
 * (c) California State University <library@calstate.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\Model\Search;

use Xerxes\Utility\Registry;

/**
 * Search Config
 *
 * @author David Walker <dwalker@calstate.edu>
 */

abstract class Config extends Registry
{
	private $facets = array();
	private $limits = array();
	private $fields = array();
	
	const UNSUPPORED_FIELD = '__UNSUPPORTED__';
	
	/**
	 * Initialize the object by picking up and processing the config xml file
	 */	
	
	public function init()
	{
		parent::init();
		
		// facets
		
		$facets = $this->xml->xpath("//config[@name='facet_fields']/facet");
		
		if ( $facets !== false )
		{
			foreach ( $facets as $facet )
			{
				$this->facets[(string) $facet["internal"]] = $facet;
			}
		}
		
		// (fixed) search limits

		$limits = $this->xml->xpath("//config[@name='limit_search_options']/facet");
		
		if ( $limits !== false )
		{
			foreach ( $limits as $limit )
			{
				$this->limits[(string) $limit["internal"]] = $limit;
			}
		}
		
		// fields
		
		$fields = $this->xml->xpath("//config[@name='basic_search_fields']/field");
		
		if ( $fields !== false )
		{
			foreach ( $fields as $field )
			{
				$this->fields[(string) $field["internal"]] = (string) $field["public"];
			}
		}
	}
	
	/**
	 * Get the ID for this config
	 */
	
	public function getID()
	{
		$config = explode('/', $this->config_file);
		return array_pop($config);
	}
	
	/**
	 * Get the defined facet type for a given facet
	 * 
	 * @param string $internal			facet internal id
	 * @return string 					type
	 */		
	
	public function getFacetType($internal)
	{
		$facet = $this->getFacet($internal);
		return (string) $facet["type"];
	}
	
	/**
	 * Whether the supplied facet is a date facet
	 * 
	 * @param string $internal			facet internal id
	 * @return bool 					true if date, false if not
	 */		
	
	public function isDateType($internal)
	{
		if ( $this->getFacetType($internal) == "date" )
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	/**
	 * Return a facet definition from the config file
	 * 
	 * @param string $internal			facet internal id
	 * @return simplexml
	 */			

	public function getFacet($internal)
	{
		if ( array_key_exists($internal, $this->facets) )
		{
			return $this->facets[$internal];
		}
		else
		{
			return null;
		}
	}

	/**
	 * Return a limit definition from the config file
	 *
	 * @param string $internal			limit internal id
	 * @return simplexml
	 */
	
	public function getLimit($internal)
	{
		if ( array_key_exists($internal, $this->limits) )
		{
			return $this->limits[$internal];
		}
		else
		{
			return null;
		}
	}	
	
	/**
	 * Return all of the facet definitions
	 * 
	 * @return array of simplexml objects
	 */		
	
	public function getFacets()
	{
		return $this->facets;
	}
	
	/**
	 * Return all of the field definitions
	 * 
	 * @return array of simplexml objects
	 */		
	
	public function getFields()
	{
		return $this->fields;
	}
	
	/**
	 * Return a specific attribute from a field definition
	 * 
	 * @param string $field      facet internal id
	 * @param string $attribute  facet attribute name
	 * 
	 * @return string if found, null if not
	 */		
	
	public function getFieldAttribute( $field, $attribute )
	{
		$values = $this->xml->xpath("//config[@name='basic_search_fields']/field[@internal='$field']/@$attribute");
		
		if ( count($values) > 0 )
		{
			return (string) $values[0];
		}
		else
		{
			return null;
		}
	}
	
	/**
	 * Return a specific attribute from a facet definition
	 *
	 * @param string $internal   facet internal id
	 * @param string $attribute  facet attribute name
	 *
	 * @return string if found, null if not
	 */
	
	public function getFacetAttribute( $internal, $attribute )
	{
		$values = $this->xml->xpath("//config/facet[@internal='$internal']/@$attribute");
	
		if ( count($values) > 0 )
		{
			return (string) $values[0];
		}
		else
		{
			return null;
		}
	}	

	/**
	 * Swap the sort id for the internal sort option
	 * 
	 * @param string $id 	public id
	 * @return string 		the internal sort option
	 */
	
	public function swapForInternalSort($id)
	{
		$config = $this->getConfig("sort_options");
		
		if ( $config != null )
		{
			foreach ( $config->option as $option )
			{
				if ( (string) $option["id"] == $id )
				{
					return (string) $option["internal"];
				}
			}			
		}
		
		// if we got this far no mapping, so return original
		
		return $id; 
	}

	/**
	 * Swap the field id for the internal field index
	 * 
	 * @param string $id 	public id
	 * @return string 		the internal field
	 */	
	
	public function swapForInternalField($id)
	{
		// asterisk is a special case
		
		if  ( $id == '*')
		{
			return $id;
		}
		
		// check basic and advanced search fields
		
		$options = array('basic_search_fields', 'advanced_search_fields');
		
		foreach ( $options as $option )
		{
			$config = $this->getConfig($option);
			
			if ( $config != null )
			{
				foreach ( $config->field as $field )
				{
					$field_id = (string) $field["id"];
					
					if ( $field_id == "")
					{
						continue;
					}
					
					// if $id was blank, then we take the first
					// one in the list, otherwise, we're looking 
					// to match
					
					elseif ( $field_id == $id || $id == "")
					{
						return (string) $field["internal"];
					}
				}			
			}
		}
		
		// if we got this far no mapping, so return unsupported
		
		return self::UNSUPPORED_FIELD; 
	}

	/**
	 * The options for the sorting mechanism
	 * 
	 * @return array
	 */
	
	public function sortOptions()
	{
		$options = array();
		
		$config = $this->getConfig("sort_options");
		
		if ( $config != null )
		{
			foreach ( $config->option as $option )
			{
				$options[] = (string) $option["id"];
			}
		}
		
		return $options;
	}	
}
