<?php
declare(strict_types=1);

/*
 * 	Voting Bundle
 *
 *	@copyright	(c) 2023 - 2024 Florian Daeumling, Germany. All right reserved
 * 	@license 	https://github.com/toteph42/voting/blob/master/LICENSE
 */

namespace VotingBundle\contao\dca;

use Contao\Backend;
use Contao\DC_Table;
use Contao\Image;
use Contao\StringUtil;
use Contao\System;
use Contao\Input;

System::loadLanguageFile('default');

$GLOBALS['TL_DCA']['tl_voting'] = [

	'config' => [
		'dataContainer'               => DC_Table::class,
		'ctable'                      => [ 'tl_voting_option' ],
		'switchToEdit'                => true,
		'enableVersioning'            => true,
		'sql' 						  => [
			'keys' 					  => [
				'id' 				  => 'primary'
			]
		]
	],

	'list' => [
		'sorting' => [
			'mode'                    => 1,
			'fields'                  => [ 'title' ],
			'flag'                    => 1,
			'panelLayout'             => 'filter;search,limit',
		],
		'label' => [
			'fields'                  => [ 'title' ],
			'format'                  => '%s',
			'label_callback'          => [ tl_voting::class, 'addStatus' ]
		],
		'global_operations' => [
			'all' => [
				'label'               => &$GLOBALS['TL_LANG']['MSC']['all'],
				'href'                => 'act=select',
				'class'               => 'header_edit_all',
				'attributes'          => 'onclick="Backend.getScrollOffset();" accesskey="e"'
			]
		],
		'operations' => [
			'edit' => [
				'label'               => &$GLOBALS['TL_LANG']['tl_voting']['edit'],
				'href'                => 'table=tl_voting_option',
				'icon'                => 'edit.gif',
				'attributes'          => 'class="contextmenu"'
			],
			'editheader' => [
				'label'               => &$GLOBALS['TL_LANG']['tl_voting']['editheader'],
				'href'                => 'act=edit',
				'icon'                => 'header.gif',
				'attributes'          => 'class="edit-header"'
			],
			'copy' => [
				'label'               => &$GLOBALS['TL_LANG']['tl_voting']['copy'],
				'href'                => 'act=copy',
				'icon'                => 'copy.gif'
			],
			'delete' => [
				'label'               => &$GLOBALS['TL_LANG']['tl_voting']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.gif',
				'attributes'          => 'onclick="if (!confirm(\''.$GLOBALS['TL_LANG']['MSC']['deleteConfirm'].'\')) return false; Backend.getScrollOffset();"'
			],
			'toggle' => [
				'label'               => &$GLOBALS['TL_LANG']['tl_voting']['toggle'],
				'icon'                => 'visible.gif',
				'attributes'          => 'onclick="Backend.getScrollOffset(); return AjaxRequest.toggleVisibility(this, %s);"',
				'button_callback'     => [ tl_voting::class, 'toggleIcon' ]
			],
			'feature' => [
				'label'               => &$GLOBALS['TL_LANG']['tl_voting']['feature'],
				'icon'                => 'featured.gif',
				'attributes'          => 'onclick="Backend.getScrollOffset(); return AjaxRequest.toggleFeatured(this, %s);"',
				'button_callback'     => [ tl_voting::class, 'iconFeatured' ]
			],
			'show' => [
				'label'               => &$GLOBALS['TL_LANG']['tl_voting']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.gif'
			]
		]
	],

	'palettes' => [
		'default'                     => '{title_legend},title,showtitle,type,votingInterval,voteMax,protected,'.
										 'featured,active_behaviorNotvotingd,active_behaviorvotingd,'.
										 'inactive_behaviorNotvotingd,inactive_behaviorvotingd;{redirect_legend:hide},'.
										 'jumpTo;{publish_legend},published,closed,activeStart,activeStop,'.
										 'showStart,showStop'
	],

	'fields' => [
		'id' => [
			'sql'                     => "int(10) unsigned NOT null auto_increment"
		],
		'tstamp' => [
			'sql'                     => "int(10) unsigned NOT null default '0'"
		],
		'lid' => [
			'sql'                     => "int(10) unsigned NOT null default '0'"
		],
		'language' => [
			'sql'                     => "varchar(2) NOT null default ''"
		],
		'title' => [
			'label'                   => &$GLOBALS['TL_LANG']['tl_voting']['title'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => [ 'mandatory' => true, 'maxlength' => 255 ],
			'sql'                     => "varchar(255) NOT null default ''"
		],
		'showtitle' => [
			'label'                   => &$GLOBALS['TL_LANG']['tl_poll']['showtitle'],
			'exclude'                 => true,
			'filter'                  => true,
			'inputType'               => 'checkbox',
			'eval'                    => array('tl_class'=>'w50'),
			'sql'                     => "char(1) NOT null default ''"
		],
		'type' => [
			'label'                   => &$GLOBALS['TL_LANG']['tl_voting']['type'],
			'default'                 => 'single',
			'exclude'                 => true,
			'inputType'               => 'select',
			'options'                 => [ 'single', 'multiple' ],
			'reference'               => &$GLOBALS['TL_LANG']['tl_voting']['type'],
			'eval'                    => [ 'tl_class' => 'w50' ],
			'sql'                     => "varchar(8) NOT null default ''"
		],
		'votingInterval' => [
			'label'                   => &$GLOBALS['TL_LANG']['tl_voting']['votingInterval'],
			'default'                 => 86400,
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => [ 'rgxp' => 'digit', 'tl_class' => 'w50' ],
			'sql'                     => "int(10) unsigned NOT null default '0'"
		],
		'voteMax' => [
			'label'                   => &$GLOBALS['TL_LANG']['tl_voting']['voteMax'],
			'default'                 => 0,
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => [ 'rgxp' => 'digit', 'tl_class' => 'w50' ],
			'sql'                     => "int(10) unsigned NOT null default '0'"
		],
		'protected' => [
			'label'                   => &$GLOBALS['TL_LANG']['tl_voting']['protected'],
			'exclude'                 => true,
			'filter'                  => true,
			'inputType'               => 'checkbox',
			'eval'                    => [ 'tl_class' => 'w50' ],
			'sql'                     => "char(1) NOT null default ''"
		],
		'featured' => [
			'label'                   => &$GLOBALS['TL_LANG']['tl_voting']['featured'],
			'exclude'                 => true,
			'filter'                  => true,
			'inputType'               => 'checkbox',
			'eval'                    => [ 'tl_class' => 'w50' ],
			'sql'                     => "char(1) NOT null default ''"
		],
		'active_behaviorNotvotingd' => [
			'label'                   => &$GLOBALS['TL_LANG']['tl_voting']['active_behaviorNotvotingd'],
 			'default'                 => 'opt1',
 			'exclude'                 => true,
			'inputType'               => 'select',
			'options'                 => [ 'opt1', 'opt2', 'opt3' ],
			'reference'               => &$GLOBALS['TL_LANG']['tl_voting']['behaviorNotvotingd'],
			'eval'                    => [ 'tl_class' => 'w50' ],
			'sql'                     => "varchar(4) NOT null default ''"
		],
		'active_behaviorvotingd' => [
			'label'                   => &$GLOBALS['TL_LANG']['tl_voting']['active_behaviorvotingd'],
			'default'                 => 'opt1',
			'exclude'                 => true,
			'inputType'               => 'select',
			'options'                 => [ 'opt1', 'opt2', 'opt3' ],
			'reference'               => &$GLOBALS['TL_LANG']['tl_voting']['behaviorvotingd'],
			'eval'                    => [ 'tl_class' => 'w50' ],
			'sql'                     => "varchar(4) NOT null default ''"
 		],
		'inactive_behaviorNotvotingd' => [
			'label'                   => &$GLOBALS['TL_LANG']['tl_voting']['inactive_behaviorNotvotingd'],
 			'default'                 => 'opt1',
 			'exclude'                 => true,
			'inputType'               => 'select',
 			'options'                 => [ 'opt1', 'opt2', 'opt3' ],
			'reference'               => &$GLOBALS['TL_LANG']['tl_voting']['behaviorNotvotingd'],
			'eval'                    => [ 'tl_class' => 'w50' ],
			'sql'                     => "varchar(4) NOT null default ''"
		],
		'inactive_behaviorvotingd' => [
			'label'                   => &$GLOBALS['TL_LANG']['tl_voting']['inactive_behaviorvotingd'],
			'default'                 => 'opt1',
			'exclude'                 => true,
			'inputType'               => 'select',
			'options'                 => [ 'opt1', 'opt2', 'opt3' ],
			'reference'               => &$GLOBALS['TL_LANG']['tl_voting']['behaviorvotingd'],
			'eval'                    => [ 'tl_class' => 'w50' ],
			'sql'                     => "varchar(4) NOT null default ''"
		],
		'jumpTo' => [
			'label'                   => &$GLOBALS['TL_LANG']['tl_module']['jumpTo'][0],
			'exclude'                 => true,
			'filter'                  => true,
			'inputType'               => 'pageTree',
			'eval'                    => [ 'fieldType' => 'radio', 'tl_class' => 'clr' ],
			'sql'                     => "int(10) unsigned NOT null default '0'"
		],
		'published' => [
			'label'                   => &$GLOBALS['TL_LANG']['tl_voting']['published'],
			'exclude'                 => true,
			'filter'                  => true,
			'inputType'               => 'checkbox',
			'eval'                    => [ 'doNotCopy' => true, 'tl_class' => 'w50' ],
			'sql'                     => "char(1) NOT null default ''"
		],
		'closed' => [
			'label'                   => &$GLOBALS['TL_LANG']['tl_voting']['closed'],
			'exclude'                 => true,
			'filter'                  => true,
			'inputType'               => 'checkbox',
			'eval'                    => [ 'doNotCopy' => true, 'tl_class' => 'w50' ],
			'sql'                     => "char(1) NOT null default ''"
		],
		'activeStart' => [
			'label'                   => &$GLOBALS['TL_LANG']['tl_voting']['activeStart'],
			'exclude'                 => true,
			'search'                  => true,
			'flag'                    => 8,
			'inputType'               => 'text',
			'eval'                    => [ 'rgxp' => 'datim', 'datepicker' => true, 'tl_class' => 'w50 wizard' ],
			'sql'                     => "varchar(10) NOT null default ''"
		],
		'activeStop' => [
			'label'                   => &$GLOBALS['TL_LANG']['tl_voting']['activeStop'],
			'exclude'                 => true,
			'search'                  => true,
			'flag'                    => 8,
			'inputType'               => 'text',
			'eval'                    => [ 'rgxp' => 'datim', 'datepicker' => true, 'tl_class' => 'w50 wizard' ],
			'sql'                     => "varchar(10) NOT null default ''"
		],
		'showStart' => [
			'label'                   => &$GLOBALS['TL_LANG']['tl_voting']['showStart'],
			'exclude'                 => true,
			'search'                  => true,
			'flag'                    => 8,
			'inputType'               => 'text',
			'eval'                    => [ 'rgxp' => 'datim', 'datepicker' => true, 'tl_class' => 'w50 wizard' ],
			'sql'                     => "varchar(10) NOT null default ''"
		],
		'showStop' => [
			'label'                   => &$GLOBALS['TL_LANG']['tl_voting']['showStop'],
			'exclude'                 => true,
			'search'                  => true,
			'flag'                    => 8,
			'inputType'               => 'text',
			'eval'                    => [ 'rgxp' => 'datim', 'datepicker' => true, 'tl_class' => 'w50 wizard' ],
			'sql'                     => "varchar(10) NOT null default ''"
		]
	]
];

class tl_voting extends Backend
{
	/**
	 * Add the voting status
	 */
	public function addStatus(array $arrRow, string $strLabel): string
	{
		if ($arrRow['closed'])
			$strLabel .= ' <span style="padding-left:3px;color:#b3b3b3;">['.
						 $GLOBALS['TL_LANG']['tl_voting']['closedvoting'] . ']</span>';

		return $strLabel;
	}

	/**
	 * Return the "feature/unfeature element" button
	 */
	public function iconFeatured(array $row, ?string $href, string $label, string $title, string $icon, string $attributes): string
	{
	 	if (($id = Input::get('fid')) && strlen($id))
	 	{
			$this->toggleFeatured(intval($id), Input::get('state'));
			$this->redirect($this->getReferer());
	   	}

		$href .= '&amp;fid='.$row['id'].'&amp;state='.($row['featured'] ? '' : 1);

		if (!$row['featured'])
			$icon = 'featured_.gif';

		return '<a href="'.$this->addToUrl($href).'" title="'.StringUtil::specialchars($title).'"'.$attributes.'>'.
				Image::getHtml($icon, $label).'</a> ';
	}

	/**
	 * Feature/unfeature a voting
	 */
	public function toggleFeatured(int $id, ?string $visible): void
	{
		$this->Database->prepare("UPDATE tl_voting SET tstamp=".time().
								 ", featured='".$visible."' WHERE id=?")->execute($id);
	}

	/**
	 * Return the "toggle visibility" button
	 */
	public function toggleIcon(array $row, ?string $href, string $label, string $title, string $icon, string $attributes): string
	{
	   	if (($id = Input::get('tid')) && strlen($id) && !Input::get('fid'))
	   	{
			$this->toggleVisibility(intval($id), Input::get('state'));
	   		$this->redirect($this->getReferer());
	   	}

		$href .= '&amp;tid='.$row['id'].'&amp;state='.($row['published'] ? '' : 1);

		if (!$row['published'])
			$icon = 'invisible.gif';

		return '<a href="'.$this->addToUrl($href).'" title="'.StringUtil::specialchars($title).'"'.$attributes.'>'.
				Image::getHtml($icon, $label).'</a> ';
	}

	/**
	 * Publish/unpublish a voting
	 */
	public function toggleVisibility(int $id, ?string $visible): void
	{
		$this->Database->prepare("UPDATE tl_voting SET tstamp=".time().
								 ", published='".$visible."' WHERE id=?")->execute($id);
	}

}
