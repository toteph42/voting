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
use Contao\Environment;
use Contao\Image;
use Contao\Input;
use Contao\System;

System::loadLanguageFile('default');
System::loadLanguageFile('tl_voting_option');

$GLOBALS['TL_DCA']['tl_voting_option'] = [

	'config' => [
		'dataContainer'             => DC_Table::class,
		'ptable'                    => 'tl_voting',
		'ctable'                    => [ 'tl_voting_results' ],
		'enableVersioning'          => true,
		'sql' 						=> [
			'keys' 					=> [
				'id' 				=> 'primary',
				'pid' 				=> 'index'
			]
		]
	],

	'list' => [
		'sorting' => [
			'mode'                    => 4,
			'fields'                  => [ 'sorting' ],
			'headerFields'            => [ 'title', 'tstamp', 'published' ],
			'child_record_callback'   => [ tl_voting_option::class, 'listVotingOptions' ]
		],
		'global_operations' => [
			'reset' => [
				'label'               => &$GLOBALS['TL_LANG']['tl_voting_option']['reset'],
				'href'                => 'key=reset',
				'icon'                => 'delete.gif',
				'attributes'          => 'onclick="if (!confirm(\''.$GLOBALS['TL_LANG']['tl_voting_option']['reset'][1].'\')) return false; Backend.getScrollOffset();"'
			],
			'all' => [
				'label'               => &$GLOBALS['TL_LANG']['MSC']['all'],
				'href'                => 'act=select',
				'class'               => 'header_edit_all',
				'attributes'          => 'onclick="Backend.getScrollOffset();" accesskey="e"'
			]
		],
		'operations' => [
			'edit' => [
				'label'               => &$GLOBALS['TL_LANG']['tl_voting_option']['edit'],
				'href'                => 'act=edit',
				'icon'                => 'edit.gif'
			],
			'copy' => [
				'label'               => &$GLOBALS['TL_LANG']['tl_voting_option']['copy'],
				'href'                => 'act=copy',
				'icon'                => 'copy.gif'
			],
			'delete' => [
				'label'               => &$GLOBALS['TL_LANG']['tl_voting_option']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.gif',
				'attributes'          => 'onclick="if (!confirm(\''.$GLOBALS['TL_LANG']['MSC']['deleteConfirm'].'\')) return false; Backend.getScrollOffset();"'
			],
			'toggle' => [
				'label'               => &$GLOBALS['TL_LANG']['tl_voting_option']['toggle'],
				'icon'                => 'visible.gif',
				'attributes'          => 'onclick="Backend.getScrollOffset(); return AjaxRequest.toggleVisibility(this, %s);"',
				'button_callback'     => [ tl_voting_option::class, 'toggleIcon' ]
			],
			'show' => [
				'label'               => &$GLOBALS['TL_LANG']['tl_voting_option']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.gif'
			],
			'voting' => [
				'label'               => &$GLOBALS['TL_LANG']['tl_voting_option']['voting'],
				'href'                => 'table=tl_voting_results',
				'icon'                => 'bundles/voting/images/icon.png'
			]
		]
	],

	'palettes' => [
		'default'                     => '{title_legend},title,published'
	],

	'fields' => [
		'id' => [
			'sql'                     => "int(10) unsigned NOT null auto_increment"
		],
		'pid' => [
			'foreignKey'              => 'tl_voting.title',
			'sql'                     => "int(10) unsigned NOT null default '0'"
		],
		'tstamp' => [
			'sql'                     => "int(10) unsigned NOT null default '0'"
		],
		'sorting' => [
			'sql'                     => "int(10) unsigned NOT null default '0'"
		],
		'lid' => [
			'sql'                     => "int(10) unsigned NOT null default '0'"
		],
		'language' => [
			'sql'                     => "varchar(2) NOT null default ''"
		],
		'title' => [
			'label'                   => &$GLOBALS['TL_LANG']['tl_voting_option']['title'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => [ 'mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50' ],
			'sql'                     => "varchar(255) NOT null default ''"
		],
		'published' => [
			'label'                   => &$GLOBALS['TL_LANG']['tl_voting_option']['published'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'eval'                    => [ 'doNotCopy' => true, 'tl_class' => 'w50 m12' ],
			'sql'                     => "char(1) NOT null default ''"
		]
	]
];

class tl_voting_option extends Backend {

    /**
     * Reset the voting and purge all votings
     */
    public function resetVoting(): void {

    	if (Input::get('key') != 'reset')
            $this->redirect($this->getReferer());

        $this->Database->prepare("DELETE FROM tl_voting_results WHERE pid IN (SELECT id FROM ".
								 "tl_voting_option WHERE pid=?)")->execute(Input::get('id'));

        $this->redirect(str_replace('&key=reset', '', Environment::get('request')));
    }

	/**
	 * List voting options
	 */
	public function listVotingOptions(array $arrRow): string {

		static $Total;
		static $voteMax = 0;

		// Get the total number of votings
		if ($Total === null) {
			$Total = $this->Database->prepare("SELECT COUNT(*) AS total FROM tl_voting_results WHERE pid IN (SELECT id FROM tl_voting_option WHERE pid=?)")
									   ->execute($arrRow['pid'])
									   ->total;
			$voteMax = $this->Database->prepare("SELECT voteMax FROM tl_voting WHERE id=?")
									  ->execute($arrRow['pid'])->voteMax;
		}

		$votings = $this->Database->prepare("SELECT COUNT(*) AS total FROM tl_voting_results WHERE pid=?")
								   ->execute($arrRow['id'])
								   ->total;

		$width = $Total ? (round(($votings / $Total), 2) * 200) : 0;
	    if (!$voteMax) {
			$width = $Total ? (round(($votings / $Total), 2) * 200) : 0;
			$prcnt = $Total ? (round(($votings / $Total), 2) * 100) : 0;
	    } else {
			$width = $voteMax ? (round(($votings / $voteMax), 2) * 200) : 0;
			$prcnt = $voteMax ? (round(($votings / $voteMax), 2) * 100) : 0;
	    }

		return '<div><div style="display:inline-block;margin-right:8px;background-color:#8AB858;height:'.
				'14px;line-height:14px;text-align:right;width:'.($width + 30).'px;">'.
				'<span style="color:#ffffff;font-size:10px;margin-right:4px;">'.$prcnt.
				' %</span></div>' . $arrRow['title'] . ' <span style="padding-left:3px;color:#b3b3b3;">['.
				sprintf(($votings == 1 ? $GLOBALS['TL_LANG']['tl_voting_option']['votingSingle'] :
				$GLOBALS['TL_LANG']['tl_voting_option']['votingPlural']), $votings).
			    ($voteMax ? ' '.$GLOBALS['TL_LANG']['MSC']['outof'].' '.$voteMax.' '.
			   	$GLOBALS['TL_LANG']['MSC']['votes'] : '').']</span></div>';
	}

	/**
	 * Return the "toggle visibility" button
	 */
	public function toggleIcon(array $row, ?string $href, string $label, string $title,
							   string $icon, string $attributes): string {

		if (($t = Input::get('tid')) && strlen($t)) {
	   		$this->toggleVisibility(intval($t), Input::get('state'));
			$this->redirect($this->getReferer());
	   	}

		$href .= '&amp;tid='.$row['id'].'&amp;state='.($row['published'] ? '' : 1);

		if (!$row['published'])
			$icon = 'invisible.gif';

		return '<a href="'.Backend::addToUrl($href).'" title="'.htmlspecialchars($title).'"'.$attributes.'>'.
				Image::getHtml($icon, $label).'</a> ';
	}

	/**
	 * Publish/unpublish a voting option
	 */
	public function toggleVisibility(int $id, ?string $visible): void {

		$this->Database->prepare("UPDATE tl_voting_option SET tstamp=".time().
								 ", published='".$visible."' WHERE id=?")->execute($id);
	}

}
