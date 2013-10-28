<?php
/**
 *
 * @package primetime
 * @copyright (c) 2013 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace primetime\primetime\core;

/**
 * @ignore
 */
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
*
*/
class primetime
{
	/**
	 * Database
	 * @var phpbb_db_driver
	 */
	protected $db;

	/**
	* Template object
	* @var \phpbb\template\template
	*/
	protected $template;

	/**
	* Utility Template object for blocks
	* @var \phpbb\template\template
	*/
	protected $btemplate;

	/**
	* Constructor
	*
	* @param \phpbb\template\template			$template				Template object
	* @param \phpbb\user                		$user       			User object
	* @param \primetime\primetime\core\primetime	$primetime				Primetime helper object
	* @param string 							$phpbb_root_path		Relative path to phpBB root
	* @param string 							$php_ext				PHP extension (php)
	*/
	public function __construct(\phpbb\db\driver\driver $db, \phpbb\template\template $template, \phpbb\template\template $btemplate)
	{
		$this->db = $db;
		$this->template = $template;
		$this->btemplate = $btemplate;
		$this->scripts = array(
			'js'	=> array(),
			'css'   => array(),
		);
	}

	/**
	 * Initialize phpBB Primetime
	 */
	public function init()
	{
		$this->template->assign_vars(array(
			'L_INDEX'			=> 'Home', //$this->user->lang['PRIMETIME_HOME'],
			'S_CMS_ENABLED'		=> true)
		);
	}

	/**
	 * include css/javascript
	 * receives an array of form: array('js' => array('test.js', 'test2.js'), 'css' => array())
	*/
	public function add_assets($scripts)
	{
		$this->scripts = array_merge_recursive($this->scripts, $scripts);
	}

	/**
	 * Pass assets to template
	 */
	public function set_assets()
	{
		global $phpbb_root_path;

		// lets clean it up
		$this->scripts['js'] = (isset($this->scripts['js'])) ? array_filter(array_unique($this->scripts['js'])) : array();
		$this->scripts['css'] = (isset($this->scripts['css'])) ? array_filter(array_unique($this->scripts['css'])) : array();

		$this->scripts = array_filter($this->scripts);

		foreach ($this->scripts as $type => $scripts)
		{
			foreach ($scripts as $file)
			{
				$this->template->assign_block_vars($type, array('UA_FILE' => trim($file)));
			}
		}

		$this->scripts = array();
	}
	
	/**
	 * Render Primetime Block
	 * 
	 * @param string $namespace extension namespace
	 * @param string $tpl_file	html template file
	 * @param string $handle	template handle
	 */
	public function render_block($namespace, $tpl_file, $handle)
	{
		$this->btemplate->set_style(array("ext/$namespace/styles"));

		$this->btemplate->set_filenames(array(
			$handle	=> $tpl_file)
		);
	
		return $this->btemplate->assign_display($handle);
	}

	/**
	 * truncate Html can truncate a string up to a number of characters while preserving whole words and HTML tags
	 * got this script from a blog that got it from a blog that got it from a blog that got it from cakephp
	 *
	 * @param string $text String to truncate.
	 * @param integer $length Length of returned string, including ellipsis.
	 * @param boolean $considerHtml If true, HTML tags would be handled correctly
	 * @param string $ending Ending to be appended to the trimmed string.
	 * @param boolean $exact If false, $text will not be cut mid-word
	 *
	 * @return string Trimmed string.
	 */
	public function truncate_html_string(&$text, $length, $considerHtml = true, $ending = '...', $exact = false)
	{
		$truncated = false;

		if ($considerHtml)
		{
			// if the plain text is shorter than the maximum length, return the whole text
			if (strlen(preg_replace('/<.*?>/', '', $text)) <= $length)
			{
				return $truncated;
			}
			// splits all html-tags to scanable lines
			preg_match_all('/(<.+?>)?([^<>]*)/s', $text, $lines, PREG_SET_ORDER);
			$total_length = strlen($ending);
			$open_tags = array();
			$truncate = '';
			foreach ($lines as $line_matchings)
			{
				// if there is any html-tag in this line, handle it and add it (uncounted) to the output
				if (!empty($line_matchings[1]))
				{
					// if it's an "empty element" with or without xhtml-conform closing slash
					if (preg_match('/^<(\s*.+?\/\s*|\s*(img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param)(\s.+?)?)>$/is', $line_matchings[1]))
					{
						// do nothing
					// if tag is a closing tag
					}
					else if (preg_match('/^<\s*\/([^\s]+?)\s*>$/s', $line_matchings[1], $tag_matchings))
					{
						// delete tag from $open_tags list
						$pos = array_search($tag_matchings[1], $open_tags);
						if ($pos !== false)
						{
							unset($open_tags[$pos]);
						}
					// if tag is an opening tag
					}
					else if (preg_match('/^<\s*([^\s>!]+).*?>$/s', $line_matchings[1], $tag_matchings))
					{
						// add tag to the beginning of $open_tags list
						array_unshift($open_tags, strtolower($tag_matchings[1]));
					}
					// add html-tag to $truncate'd text
					$truncate .= $line_matchings[1];
				}
				// calculate the length of the plain text part of the line; handle entities as one character
				$content_length = strlen(preg_replace('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', ' ', $line_matchings[2]));
				if ($total_length + $content_length > $length)
				{
					// the number of characters which are left
					$left = $length - $total_length;
					$entities_length = 0;
					// search for html entities
					if (preg_match_all('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', $line_matchings[2], $entities, PREG_OFFSET_CAPTURE))
					{
						// calculate the real length of all entities in the legal range
						foreach ($entities[0] as $entity)
						{
							if ($entity[1]+1-$entities_length <= $left)
							{
								$left--;
								$entities_length += strlen($entity[0]);
							}
							else
							{
								// no more characters left
								break;
							}
						}
					}
					$truncate .= substr($line_matchings[2], 0, $left+$entities_length);
					// maximum lenght is reached, so get off the loop
					break;
				}
				else
				{
					$truncate .= $line_matchings[2];
					$total_length += $content_length;
				}
				// if the maximum length is reached, get off the loop
				if($total_length>= $length)
				{
					break;
				}
			}
		}
		else
		{
			if (strlen($text) <= $length)
			{
				return $truncated;
			}
			else
			{
				$truncate = substr($text, 0, $length - strlen($ending));
			}
		}
		// if the words shouldn't be cut in the middle...
		if (!$exact)
		{
			// ...search the last occurance of a space...
			$spacepos = strrpos($truncate, ' ');
			if (isset($spacepos))
			{
				// ...and cut the text in this position
				$truncate = substr($truncate, 0, $spacepos);
			}
		}
		// add the defined ending to the text
		$truncate .= $ending;
		if ($considerHtml)
		{
			// close all unclosed html-tags
			foreach ($open_tags as $tag)
			{
				$truncate .= '</' . $tag . '>';
			}
		}
		$text = $truncate;

		return true;
	}

	/**
	* Add a secret token to the form (requires the S_FORM_TOKEN template variable)
	* @param string  $form_name The name of the form; has to match the name used in check_form_key, otherwise no restrictions apply
	*/
	public function mod_add_form_key($form_name)
	{
		add_form_key($form_name);
		$s_form_token = $this->template->_tpldata['.']['0']['S_FORM_TOKEN'];
		$this->btemplate->assign_var('S_FORM_TOKEN', $s_form_token);

		return $s_form_token;
	}

	/**
	 * Merge dbal query arrays
	*/
	public function merge_dbal_arrays($sql_ary1, $sql_ary2)
	{
		if (sizeof($sql_ary2))
		{
			$sql_ary1['SELECT'] .= (!empty($sql_ary2['SELECT'])) ? ', ' . $sql_ary2['SELECT'] : '';
			$sql_ary1['FROM'] += (!empty($sql_ary2['FROM'])) ? $sql_ary2['FROM'] : array();
			$sql_ary1['WHERE'] .= (!empty($sql_ary2['WHERE'])) ? ' AND ' . $sql_ary2['WHERE'] : '';
	
			if (!empty($sql_ary2['LEFT_JOIN']))
			{
				$sql_ary1['LEFT_JOIN'] = (!empty($sql_ary1['LEFT_JOIN'])) ? array_merge($sql_ary1['LEFT_JOIN'], $sql_ary2['LEFT_JOIN']) : $sql_ary2['LEFT_JOIN'];
			}
	
			if (!empty($sql_ary2['GROUP_BY']))
			{
				$sql_ary1['GROUP_BY'] = $sql_ary2['GROUP_BY'];
			}
	
			if (!empty($sql_ary2['ORDER_BY']))
			{
				$sql_ary1['ORDER_BY'] = $sql_ary2['ORDER_BY'];
			}
		}

		return $sql_ary1;
	}
}