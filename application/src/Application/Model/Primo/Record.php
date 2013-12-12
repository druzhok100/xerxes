<?php

/*
 * This file is part of Xerxes.
 *
 * (c) California State University <library@calstate.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\Model\Primo;

use Xerxes;
use Xerxes\Utility\Parser;

/**
 * Primo Record
 * 
 * @author David Walker <dwalker@calstate.edu>
 */

class Record extends Xerxes\Record
{
	protected $source = "primo";
	
	public function map()
	{
		// score
		
		$this->score = (string) $this->document->documentElement->getAttribute("RANK");
		
		// record data
		
		$record = $this->document->documentElement->getElementsByTagName("record")->item(0);
		
		// print $this->document->saveXML();
		
		$display = $this->getElement($record, "display");
		$search = $this->getElement($record, "search");
		$sort = $this->getElement($record, "sort");
		$addata = $this->getElement($record, "addata");
		$facets = $this->getElement($record, "facets");
		
		if ( $display != null)
		{
			// database name
			
			$this->database_name = $this->getElementValue($display,"source");
			$this->database_name = strip_tags($this->database_name);
			
			// journal
			
			$this->journal = $this->getElementValue($display,"ispartof");
			
			// snippet
			
			$this->snippet = $this->getElementValue($display,"snippet"); 
			$this->snippet = strip_tags($this->snippet);
			
			// description
			
			$this->abstract = $this->getElementValue($display,"description");
			$this->abstract = strip_tags($this->abstract);
			
			// language

			$this->language = $this->getElementValue($display,"language");
			
			// peer reviewed
			
			$peer_reviewed = $this->getElementValue($display,'lds50');
			
			if ( $peer_reviewed == 'peer_reviewed' )
			{
				$this->refereed = true;
			}
		}
		
		if ( $search != null)
		{
			// record id
			
			$this->record_id = $this->getElementValue($search,"recordid");
			
			// year
			
			$this->year = $this->getElementValue($search,"creationdate");
			
			// issn
			
			$issn = $this->getElementValue($search,"issn");
			$issn = preg_replace('/\D/', "", $issn);
			array_push($this->issns, $issn);
			
			// authors
			
			$authors = $this->getElementValues($search,"creatorcontrib");
			
			foreach ( $authors as $author )
			{
				array_push($this->authors, new Xerxes\Record\Author($author, null, "personal"));
			}
			
			// format
			
			$format = $this->getElementValue($search,"rsrctype");
			$this->format()->setInternalFormat($format);
			
			// create a readable display
			
			$format_display = self::createReadableLabel($format);
			$this->format()->setPublicFormat($format_display);
		}		
		
		// article data
		
		if ( $addata != null)
		{
			$this->journal_title = $this->start_page = $this->getElementValue($addata,"jtitle");
			$this->volume = $this->getElementValue($addata,"volume");
			$this->issue = $this->getElementValue($addata,"issue");
			$this->start_page = $this->getElementValue($addata,"spage");
			$this->end_page = $this->getElementValue($addata,"epage");
			
			// primo's own ris type
			
			$ris_type = $this->getElementValue($addata,'ristype');
			
			if ( $ris_type != "" )
			{
				$this->format()->setNormalizedFormat($ris_type);
			}
			
			// abstract 
			
			$abstract = $this->getElementValue($addata,"abstract");
			
			if ( $this->abstract == "" ) // only take this one if none set above
			{
				$this->abstract = strip_tags($abstract);
			}

			// gale madness
			
			if ( stristr($this->database_name, "gale") )
			{
				if ( strpos($this->abstract, 'the full-text of this article') !== false )
				{
					$this->abstract = Parser::removeLeft($this->abstract, 'Abstract:');
				}
			}
		}
		
		// subjects
		
		if ( $facets != null )
		{
			$topics = $this->getElementValues($facets,"topic");
			
			foreach ( $topics as $topic )
			{
				$subject_object = new Xerxes\Record\Subject();
				$subject_object->value = $topic;
				$subject_object->display = $topic;
				
				array_push(	$this->subjects, $subject_object);
			}
			
		}

		// title
		
		if ( $sort != null)
		{
			$this->title = $this->getElementValue($sort,"title");
		}
		
		// Gale title clean-up, because for some reason unknown to man they put weird 
		// notes and junk at the end of the title. so remove them here and add them to notes.		
		
		// @todo factor this out somehow? since this is very similar to Gale MetalibRecord code
		
		if ( stristr($this->database_name, "gale") || stristr($this->database_name, "muse"))
		{
			$strGaleRegExp = '/\(([^)]*)\)/';
			$arrMatches = array();
	
			if (preg_match_all ( $strGaleRegExp, $this->title, $arrMatches ) != 0)
			{
				$this->title = preg_replace ( $strGaleRegExp, "", $this->title );
				
				foreach ( $arrMatches[1] as $strMatch )
				{
					array_push($this->notes, $strMatch);
				}
			}
		}
	}
	
	public static function createReadableLabel($format)
	{
		$format_display = str_replace('audio_video', 'Audio/Video', $format);
			
		$format_array = explode('_', $format_display);
			
		for ( $x = 0; $x < count($format_array); $x++ )
		{
			$format_array[$x] = ucfirst($format_array[$x]);
		}
			
		$format_display = implode(' ', $format_array);
		
		return $format_display;
	}
	
	protected function getElement($node, $name)
	{
		$elements = $node->getElementsByTagName($name);
		
		if ( count($elements) > 0 )
		{
			return $elements->item(0);
		}
		else
		{
			return null;
		}
	}
	
	protected function getElementValue($node, $name)
	{
		$element = $this->getElement($node, $name);
		
		if ( $element != null )
		{
			return $element->nodeValue;
		}
		else
		{
			return null;
		}
	}
	
	protected function getElementValues($node, $name)
	{
		$values = array();
		
		$elements = $node->getElementsByTagName($name);
		
		foreach ( $elements as $node )
		{
			array_push($values, $node->nodeValue);
		}
		
		return $values;
	}		
}
