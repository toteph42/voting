<?php
declare(strict_types=1);

/*
 * 	Voting Bundle
 *
 *	@copyright	(c) 2023 - 2024 Florian Daeumling, Germany. All right reserved
 * 	@license 	https://github.com/toteph42/voting/blob/master/LICENSE
 */

/**
 * Add a palette to tl_content
 */
use Contao\Backend;
use Toteph42\VotingBundle\Controller\ContentElement\VotingIncludeElement;

$GLOBALS['TL_DCA']['tl_content']['palettes'][VotingIncludeElement::TYPE] = '
	{type_legend},type,headline;{include_legend},
	voting,voting_current;
	{protected_legend:hide},protected;
	{expert_legend:hide},guests,cssID,space';

/**
 * Add a field to tl_content
 */
$GLOBALS['TL_DCA']['tl_content']['fields']['voting'] = [
	'label'                   => &$GLOBALS['TL_LANG']['tl_content']['voting'],
	'exclude'                 => true,
	'inputType'               => 'select',
	'options_callback'        => [ tl_content_voting::class , 'getVotings' ],
	'eval'                    => [ 'includeBlankOption' => true, 'chosen' => true, 'tl_class' => 'w50' ],
	'sql'                     => "int(10) unsigned NOT null default '0'"
];

$GLOBALS['TL_DCA']['tl_content']['fields']['voting_current'] = [
	'label'                   => &$GLOBALS['TL_LANG']['tl_content']['voting_current'],
	'exclude'                 => true,
	'inputType'               => 'checkbox',
	'eval'                    => [ 'tl_class' => 'w50 m12' ],
	'sql'                     => "char(1) NOT null default ''"
];

/**
 * Provide miscellaneous methods that are used by the data configuration array.
 */
class tl_content_voting extends Backend {

	/**
	 * Get all votings and return them as array
	 */
	public function getVotings(): array {

		$arr = [];

		$obj= $this->Database->execute("SELECT id, title FROM tl_voting ORDER BY title");

		while ($obj->next())
			$arr[$obj->id] = $obj->title;

		return $arr;
	}

}
