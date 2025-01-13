<?php
declare(strict_types=1);

/*
 * 	This File is part of Toteph42 This File is part of Toteph42 Voting bundle
 *
 *	@copyright	(c) 2024 Florian Daeumling, Germany. All right reserved
 * 	@license 	https://github.com/toteph42/voting/blob/master/LICENSE
 */

use Contao\CoreBundle\DataContainer\PaletteManipulator;

// Extend the default palette
PaletteManipulator::create()
    ->addField('voting_share,voting_alias', 'personal_legend', PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('default', 'tl_member')
;

// Add field for voting shares
$GLOBALS['TL_DCA']['tl_member']['fields']['voting_share'] = [
	'label' 		=> &$GLOBALS['TL_LANG']['tl_member']['voting_share'],
	'inputType'     => 'text',
	'eval'          => [ 'rgxp'	=> 'digit', 'tl_class' => 'w50', ],
	'sql'           => [ 'type' => 'float', 'default' => '1', ],
];

// Add field for voting alias
$GLOBALS['TL_DCA']['tl_member']['fields']['voting_alias'] = [
	'label' 		=> &$GLOBALS['TL_LANG']['tl_member']['voting_alias'],
	'inputType'     => 'text',
	'eval'          => [ 'tl_class' => 'w50', ],
	'sql'           => [ 'type' => 'string', 'default' => '', ],
];

