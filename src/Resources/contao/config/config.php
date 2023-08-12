<?php
declare(strict_types=1);

/*
 * 	Voting Bundle
 *
 *	@copyright	(c) 2023 Florian Daeumling, Germany. All right reserved
 * 	@license 	https://github.com/toteph42/voting/blob/master/LICENSE
 */

namespace Toteph42\VotingBundle;

use Toteph42\VotingBundle\Controller\ContentElement\VotingIncludeElement;
use VotingBundle\contao\dca\tl_voting_option;

/**
 * Back end modules
 */
$GLOBALS['BE_MOD']['content'][VotingIncludeElement::TYPE] = [
		'tables' => [ 'tl_voting', 'tl_voting_option', 'tl_voting_results' ],
		'icon'   => 'bundles/voting/images/icon.png',
		'reset'  => [ tl_voting_option::class, 'resetVoting' ]
];
