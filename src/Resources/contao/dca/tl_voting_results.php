<?php
declare(strict_types=1);

/*
 * 	Voting Bundle
 *
 *	@copyright	(c) 2023 Florian Daeumling, Germany. All right reserved
 * 	@license 	https://github.com/toteph42/voting/blob/master/LICENSE
 */

namespace VotingBundle\contao\dca;

use Contao\Backend;
use Contao\DC_Table;
use Contao\DataContainer;
use Contao\System;

System::loadLanguageFile('tl_voting_results');

$GLOBALS['TL_DCA']['tl_voting_results'] = [

	'config' => [
		'dataContainer'             => DC_Table::class,
		'ptable'                    => 'tl_voting_option',
		'closed'                    => true,
		'doNotCopyRecords'          => true,
		'notEditable'               => true,
		'onload_callback' 			=> [
									   [ tl_voting_results::class, 'filterItemsByParent' ]
		],
		'sql' 						=> [
			'keys' 					=> [
				'id' 				=> 'primary',
				'pid' 				=> 'index'
			]
		]
	],

	'list' => [
		'sorting' => [
			'mode'                    => 1,
			'fields'                  => [ 'tstamp' ],
			'flag'                    => 12,
			'panelLayout'             => 'filter;search,limit',
		],
		'label' => [
			'fields'                  => [ 'tstamp', 'ip', 'member' ],
			'showColumns'             => true,
			'label_callback'          => [ tl_voting_results::class, 'addMemberUsername' ]
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
			'delete' => [
				'label'               => &$GLOBALS['TL_LANG']['tl_voting_results']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.gif',
				'attributes'          => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"'
			],
			'show' => [
				'label'               => &$GLOBALS['TL_LANG']['tl_voting_results']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.gif'
			]
		]
	],

	'fields' => [
		'id' => [
			'sql'                     => "int(10) unsigned NOT null auto_increment"
		],
		'pid' => [
			'foreignKey'              => 'tl_voting_option.title',
			'sql'                     => "int(10) unsigned NOT null default '0'"
		],
		'tstamp' => [
			'sql'                     => "int(10) unsigned NOT null default '0'"
		],
		'tstamp' => [
			'label'                   => &$GLOBALS['TL_LANG']['tl_voting_results']['tstamp'],
			'exclude'                 => true,
			'filter'                  => true,
			'flag'                    => 8,
			'sql'                     => "int(10) unsigned NOT null default '0'"
		],
		'ip' => [
			'label'                   => &$GLOBALS['TL_LANG']['tl_voting_results']['ip'],
			'exclude'                 => true,
			'filter'                  => true,
			'search'                  => true,
			'sql'                     => "varchar(16) NOT null default ''"
		],
		'member' => [
			'label'                   => &$GLOBALS['TL_LANG']['tl_voting_results']['member'],
			'exclude'                 => true,
			'filter'                  => true,
			'search'                  => true,
			'foreignKey'              => 'tl_member.username',
			'reference'               => [ 0 => $GLOBALS['TL_LANG']['tl_voting_results']['anonymous'] ],
			'sql'                     => "int(10) unsigned NOT null default '0'"
		]
	]
];

class tl_voting_results extends Backend {

	/**
	 * Limit the displayed items so filter panel can handle things correctly
	 */
	public function filterItemsByParent(): void {

		$GLOBALS['TL_DCA']['tl_voting_results']['list']['sorting']['root'] =
				$this->Database->prepare("SELECT id FROM tl_voting_results WHERE pid=?")->execute($this->Input->get('id'))->fetchEach('id');
	}

	/**
	 * Add a member username
	 */
	public function addMemberUsername(array $row, string $label, DataContainer $dc, array $args): array {

		if ($row['member']) {
			$objMember = $this->Database->prepare("SELECT * FROM tl_member WHERE id=?")
										->execute($row['member']);

			if ($objMember->numRows)
				$args[2] = '<a href="contao/main.php?do=member&act=show&id='.$row['member'].'&rt='.
							REQUEST_TOKEN.'">'.$objMember->username.' (ID '.$row['member'].')</a>';
		}

		return $args;
	}

}
