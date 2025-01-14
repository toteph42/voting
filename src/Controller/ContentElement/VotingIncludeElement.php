<?php
declare(strict_types=1);

/*
 * 	This File is part of Toteph42 Voting bundle
 *
 *	@copyright	(c) Florian Daeumling, Germany. All right reserved
 * 	@license 	https://github.com/toteph42/voting/blob/master/LICENSE
 */

namespace Toteph42\VotingBundle\Controller\ContentElement;

use Contao\ContentModel;
use Contao\Database;
use Contao\Environment;
use Contao\FrontendUser;
use Contao\Input;
use Contao\StringUtil;
use Contao\System;
use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController;
use Contao\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Contao\FrontendTemplate;

class VotingIncludeElement extends AbstractContentElementController
{

	public const TYPE = 'voting';

	private string $Cookie = 'CONTAO_VOTING_';
    private ?Database $db = null;
	protected $obj;

	protected function getResponse(Template $template, ContentModel $model, Request $request): Response
	{
		$scopeMatcher = System::getContainer()->get('contao.routing.scope_matcher');
		$request = System::getContainer()->get('request_stack')->getMainRequest();

		if ($request && $scopeMatcher->isBackendRequest($request))
			$template->voting = '### VOTING ###';
		else
		{

			if (!$this->db)
        		$this->db = Database::getInstance();

			$this->obj = $this->db->prepare($this->getVotingQuery('tl_voting'))
										   ->limit(1)
										   ->execute($template->voting);

			if ($this->obj->numRows && $this->obj->options)
			{
				$template = new FrontendTemplate('voting_default');
				$template->setData($this->obj->row());
			}

			// Default is not to show anything
			$show = false;
			$template->cssTyp = 'standard';
			$template->cssMsg = '';
			$template->message = '';
			$template->showResults = $show;
			$template->showForm = false;

			// Save Request token
			$template->requestToken = System::getContainer()->get('contao.csrf.token_manager')->getDefaultTokenValue();

			$tokenChecker = System::getContainer()->get('contao.security.token_checker');

			// Display a "login to voting" message
			if ($this->obj->protected && !$tokenChecker->hasFrontendUser())
			{
				$template->cssTyp = 'protected';
				$template->cssMsg = 'login';
				$template->message = $GLOBALS['TL_LANG']['MSC']['loginToVote'];
			}

			$time = time();
			$ena = ($this->obj->closed || (($this->obj->activeStart != '' &&
				    $this->obj->activeStart > $time) || ($this->obj->activeStop != '' &&
					$this->obj->activeStop < $time))) ? false : true;
			$strFormId = 'voting_' . $this->obj->id;
			$template->title = $this->obj->showtitle == '1' ? $this->obj->title : null;
			$template->active = $ena;
			if ($this->obj->featured )
				$template->cssTyp = 'featured';

			// Display a message if the voting is disabled
			if (!$ena)
			{
				$template->cssTyp = 'closed';
				$template->cssMsg = 'isclosed';
				$template->message = $GLOBALS['TL_LANG']['MSC']['votingClosed'];
			}

			// Display a confirmation message
			if (isset($_SESSION['voting'][$this->obj->id]))
			{
				$blnJustvoted = true;
				$template->cssMsg = 'confirm';
				$template->message = $_SESSION['voting'][$this->obj->id];
				unset($_SESSION['voting'][$this->obj->id]);

			} else
				$blnJustvoted = true;

			$template->hasVoted = $voting = $this->hasVoted();

			// Check if we should display the results
			if (($ena && !$voting &&
				(($this->obj->active_behaviornotvoted == 'opt1' && Input::get('results') == $this->obj->id) ||
				($this->obj->active_behaviornotvoted == 'opt3' && (!Input::get('voting') || Input::get('voting') != $this->obj->id)))) ||
				($ena && $voting && (($this->obj->active_behaviorvoting == 'opt2' && Input::get('results') == $this->obj->id) ||
				($this->obj->active_behaviorvoting == 'opt1' && ($blnJustvoted || !Input::get('voting') || Input::get('voting') != $this->obj->id)))) ||
				(!$ena && !$voting && (($this->obj->inactive_behaviornotvoted == 'opt1' && Input::get('results') == $this->obj->id) ||
				($this->obj->inactive_behaviornotvoted == 'opt3' && (!Input::get('voting') || Input::get('voting') != $this->obj->id)))) ||
				(!$ena && $voting && (($this->obj->inactive_behaviorvoting == 'opt2' && Input::get('results') == $this->obj->id) ||
				($this->obj->inactive_behaviorvoting == 'opt1' && (!Input::get('voting') || Input::get('voting') != $this->obj->id)))))
				$show = true;

			$opts = $this->db->prepare($this->getVotingQuery('tl_voting_option'))->execute($this->obj->id);

			// Display results under certain circumstances
			if ($show)
			{
				$arrResults = [];
				$allvotes = array_sum($opts->fetchEach('voting'));
				$opts->reset();

				System::loadLanguageFile('tl_voting_option');

				// Generate results
				while ($opts->next())
				{
					if ($opts->voting === null)
						$opts->voting = 0;

					if (!$this->obj->voteMax)
						$arrResults[] = [
							'title'   	=> $opts->title,
							'voting' 	=> sprintf($GLOBALS['TL_LANG']['tl_voting_option']['votings'], $opts->voting),
							'prcnt'   	=> ($allvotes > 0) ? (round(($opts->voting / $allvotes), 2) * 100) : 0,
						];
					else
						$arrResults[] = [
							'title' 	=> $opts->title,
							'prcnt' 	=> ($allvotes > 0) ? (round(($opts->voting / $this->obj->voteMax), 2) * 100) : 0,
							'outof'		=> $opts->voting.' '.$GLOBALS['TL_LANG']['MSC']['outof'].' '.
									       $this->obj->voteMax.' '.$GLOBALS['TL_LANG']['MSC']['votes'],
						];
				}

				$template->showResults = $show;
				$template->total = $voting;
				$template->results = $arrResults;
				$template->formLink = '';

				// Display the form link
				if ($ena)
					$template->formLink = sprintf('<a href="%s" class="vote_link" title="%s">%s</a>',
											 	  $this->generateVotingUrl('voting'),
												  StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['showForm']),
											 	  $GLOBALS['TL_LANG']['MSC']['showForm']);

				return $template->getResponse();
			}

			$arrOptions = [];

			// Generate options
			while ($opts->next())
				$arrOptions[$opts->id] = $opts->title;

			// Options form field
			$arrField = [
				'name' 		=> 'options',
				'options' 	=> $arrOptions,
				'inputType' => ($this->obj->type == 'single') ? 'radio' : 'checkbox',
				'eval' 		=> [ 'mandatory'=>true ]
			];

			$doNotSubmit = false;
			$objWidget = new $GLOBALS['TL_FFL'][$arrField['inputType']](
							 $GLOBALS['TL_FFL'][$arrField['inputType']]::getAttributesFromDca($arrField,
							 $arrField['name']));

			// Override the ID parameter to avoid ID duplicates for radio buttons and labels
			$objWidget->id = 'voting_' . $this->obj->id;

			// Validate the widget
			if (Input::post('FORM_SUBMIT') == $strFormId && !Input::post('results'))
			{
				$objWidget->validate();

				if ($objWidget->hasErrors())
					$doNotSubmit = true;
			}

			$template->showForm = true;
			$template->options = $objWidget;
			$template->submit = (!$ena || $voting || ($this->obj->protected &&
								 !$tokenChecker->hasFrontendUser())) ? '' : $GLOBALS['TL_LANG']['MSC']['voteNow'];
			$template->action = StringUtil::ampersand(Environment::get('request'));
			$template->formId = $strFormId;
			$template->hasError = $doNotSubmit;
			$template->resultsLink = '';
			$template->backLink = sprintf('<a href="%s" class="back_link" title="%s">%s</a>',
										 $this->generateVotingUrl('', false),
										 StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['backBTTitle']),
										 $GLOBALS['TL_LANG']['MSC']['backBT']);

			// Display the results link
			if (($ena && !$voting && $this->obj->active_behaviornotvoted == 'opt1') ||
				($ena && $voting && $this->obj->active_behaviorvoting == 'opt2') ||
				(!$ena && !$voting && $this->obj->inactive_behaviornotvoted == 'opt1') ||
				(!$ena && $voting && $this->obj->inactive_behaviorvoting == 'opt2'))
				$template->resultsLink = sprintf('<a href="%s" class="result_link" title="%s">%s</a>',
											$this->generateVotingUrl('results'),
											StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['showResults']),
											$GLOBALS['TL_LANG']['MSC']['showResults']);

			// Add the voting
			if (Input::post('FORM_SUBMIT') == $strFormId && !$doNotSubmit)
			{
				if (!$ena || $voting || ($this->obj->protected && !$tokenChecker->hasFrontendUser()))
					$this->reload();

				$arrValues = is_array($objWidget->value) ? $objWidget->value : array($objWidget->value);

				// Set the cookie
				Input::setCookie($this->Cookie.$this->obj->id, $time, ($time + (365 * 86400)));

				// Delete existing votings
				$this->db->prepare(
							'DELETE vr FROM tl_voting_results vr '.
							'JOIN tl_voting_option vo ON vr.pid = vo.id '.
							'JOIN tl_member m ON vr.member = m.id '.
							'JOIN tl_member m2 ON m.voting_alias = m2.voting_alias '.
							'WHERE vo.pid = ? AND m2.id = ?')
							->execute($this->obj->id, FrontendUser::getInstance()->id);

				// Store the voting
	            foreach ($arrValues as $value)
	            {
	    			$arrSet = [
	    				'pid' 		=> $value,
	    				'tstamp' 	=> $time,
	    				'ip' 		=> Environment::get('ip'),
	    				'member' 	=> $tokenChecker->hasFrontendUser() ? FrontendUser::getInstance()->id : 0,
	    			];

	    			$this->db->prepare('INSERT INTO tl_voting_results %s')->set($arrSet)->execute();
	            }

				// Redirect or reload the page
				$_SESSION['voting'][$this->obj->id] = $GLOBALS['TL_LANG']['MSC']['votingSubmitted'];

				$template->reload();
			}
		}

		return $template->getResponse();
    }

    /**
     * Check if user has already voted within time window
     */
	public function hasVoted(): bool
	{

		$expire = $this->obj->votingInterval ? (time() - $this->obj->votingInterval) : 0;

		// Check the cookie
		if (Input::cookie($this->Cookie.$this->obj->id) > $expire)
			return true;

		$tokenChecker = System::getContainer()->get('contao.security.token_checker');
		if ($this->obj->protected && $tokenChecker->hasFrontendUser())
		{
			$x = 1;
		    $objvoting = $this->db->prepare(
            				'SELECT * '.
            				'FROM tl_voting_results '.
            				'WHERE member = ? '.
            				'AND tstamp > ? '.
            				'AND pid IN '.
            					'(SELECT id '.
            					'FROM tl_voting_option '.
            					'WHERE pid = ? '.(!$tokenChecker->hasBackendUser() ? 'AND published = 1' : '').') '.
            					'ORDER BY tstamp DESC')->limit(1)->execute(FrontendUser::getInstance()->id, $expire, $this->obj->id);
		} else {
			$x = $this->obj->protected;
			$x = $tokenChecker->hasFrontendUser();
			$objvoting = $this->db->prepare(
    						'SELECT * '.
    						'FROM tl_voting_results '.
    						'WHERE ip = ? '.
    						'AND tstamp > ? '.
    						'AND pid IN '.
    							'(SELECT id '.
								'FROM tl_voting_option '.
    							'WHERE pid = ? '.(!$tokenChecker->hasBackendUser() ? 'AND published = 1' : '').') '.
    							'ORDER BY tstamp DESC')->limit(1)->execute(Environment::get('ip'), $expire, $this->obj->id);
		}

		// User has already voted
		if ($objvoting->numRows)
			return true;

		return false;
	}

	/**
	 * Generate the voting URL and return it as string
	 */
	protected function generateVotingUrl(string $key, bool $addKey = true): string
	{

		$arr = explode('?', Environment::get('request'), 2);
		$strPage = $arr[0];
		$strQuery = count($arr) == 2 ? $arr[1] : null;
		$arrQuery = [];

        // parse the current query
        if ($strQuery != '')
        {

            $arrQuery = explode('&', $strQuery);

            // remove the "voting" and "results" parameters
            foreach ($arrQuery as $k => $v) {
                list($key, $value) = explode('=', $v, 2);

                if ($key == 'voting' || $key == 'results')
                    unset($arrQuery[$k]);
            }
            $value;
        }

        // Add the key
        if ($addKey)
	        $arrQuery[] = $key . '=' . $this->obj->id;

		return StringUtil::ampersand($strPage . '?' . implode('&', $arrQuery));
	}

	/**
	 * Generate a select statement that includes translated fields
	 */
	protected function getVotingQuery(string $strTable): string
	{

		$chk = System::getContainer()->get('contao.security.token_checker');
        switch ($strTable)
        {
		case 'tl_voting':
			$strQuery = 'SELECT *, (SELECT COUNT(*) FROM tl_voting_option '.
						'WHERE pid=tl_voting.id) AS options '.
						'FROM tl_voting '.
						'WHERE id = ?'.($chk->hasFrontendUser() ? ' AND published = 1' : '');
			break;

		case 'tl_voting_option':
			$strQuery = 'SELECT *, '.
						'(SELECT SUM(voting_share) FROM (tl_member, tl_voting_results) '.
							'WHERE tl_member.id = tl_voting_results.member '.
							'AND pid = tl_voting_option.id) '.
							'AS voting '.
						'FROM tl_voting_option '.
							'WHERE pid = ? '.($chk->hasFrontendUser() ? 'AND published = 1 ' : '').
							'ORDER BY sorting';
			break;
		}

		return $strQuery;
	}

}
