<?php
declare(strict_types=1);

/*
 * 	Voting Bundle
 *
 *	@copyright	(c) 2023 - 2024 Florian Daeumling, Germany. All right reserved
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

			// default is not to show anything
			$show = false;
			$template->cssTyp = 'standard';
			$template->cssMsg = '';
			$template->message = '';
			$template->showResults = $show;
			$template->showForm = false;

			$tokenChecker = System::getContainer()->get('contao.security.token_checker');

			// display a "login to voting" message
			if ($this->obj->protected && !$tokenChecker->hasFrontendUser())
			{
				$template->cssTyp = 'protected';
				$template->cssMsg = 'login';
				$template->message = $GLOBALS['TL_LANG']['MSC']['loginTovoting'];
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

			// display a message if the voting is disabled
			if (!$ena)
			{
				$template->cssTyp = 'closed';
				$template->cssMsg = 'isclosed';
				$template->message = $GLOBALS['TL_LANG']['MSC']['votingClosed'];
			}

			// Display a confirmation message
			if (isset($_SESSION['voting'][$this->obj->id]))
			{
				$blnJustvotingd = true;
				$template->cssMsg = 'confirm';
				$template->message = $_SESSION['voting'][$this->obj->id];
				unset($_SESSION['voting'][$this->obj->id]);
			}

			$template->hasVoted = $voting = $this->hasVoted();

			// check if we should display the results
			if (($ena && !$voting &&
				(($this->obj->active_behaviorNotvotingd == 'opt1' && Input::get('results') == $this->obj->id) ||
				($this->obj->active_behaviorNotvotingd == 'opt3' && (!Input::get('voting') || Input::get('voting') != $this->obj->id)))) ||
				($ena && $voting && (($this->obj->active_behaviorvotingd == 'opt2' && Input::get('results') == $this->obj->id) ||
				($this->obj->active_behaviorvotingd == 'opt1' && ($blnJustvotingd || !Input::get('voting') || Input::get('voting') != $this->obj->id)))) ||
				(!$ena && !$voting && (($this->obj->inactive_behaviorNotvotingd == 'opt1' && Input::get('results') == $this->obj->id) ||
				($this->obj->inactive_behaviorNotvotingd == 'opt3' && (!Input::get('voting') || Input::get('voting') != $this->obj->id)))) ||
				(!$ena && $voting && (($this->obj->inactive_behaviorvotingd == 'opt2' && Input::get('results') == $this->obj->id) ||
				($this->obj->inactive_behaviorvotingd == 'opt1' && (!Input::get('voting') || Input::get('voting') != $this->obj->id)))))
				$show = true;

			$Options = $this->db->prepare($this->getVotingQuery('tl_voting_option'))->execute($this->obj->id);

			// Display results under certain circumstances
			if ($show)
			{
				$arrResults = [];
				$voting = array_sum($Options->fetchEach('voting'));
				$Options->reset();

				System::loadLanguageFile('tl_voting_option');

				// Generate results
				while ($Options->next())
				{
					if (!$this->obj->voteMax)
						$arrResults[] = [
							'title'   	=> $Options->title,
							'voting' 	=> sprintf($Options->voting > 1 ? $GLOBALS['TL_LANG']['tl_voting_option']['votingPlural'] :
										   $GLOBALS['TL_LANG']['tl_voting_option']['votingPlural'], $Options->voting),
							'prcnt'   	=> ($voting > 0) ? (round(($Options->voting / $voting), 2) * 100) : 0,
						];
					else
						$arrResults[] = [
							'title' 	=> $Options->title,
							'prcnt' 	=> ($voting > 0) ? (round(($Options->voting / $this->obj->voteMax), 2) * 100) : 0,
							'outof'		=> $Options->voting.' '.$GLOBALS['TL_LANG']['MSC']['outof'].' '.
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
			while ($Options->next())
				$arrOptions[$Options->id] = $Options->title;

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
			if (($ena && !$voting && $this->obj->active_behaviorNotvotingd == 'opt1') ||
				($ena && $voting && $this->obj->active_behaviorvotingd == 'opt2') ||
				(!$ena && !$voting && $this->obj->inactive_behaviorNotvotingd == 'opt1') ||
				(!$ena && $voting && $this->obj->inactive_behaviorvotingd == 'opt2'))
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

	            // Store the voting
	            foreach ($arrValues as $value)
	            {
	    			$arrSet = [
	    				'pid' 		=> $value,
	    				'tstamp' 	=> $time,
	    				'ip' 		=> Environment::get('ip'),
	    				'member' 	=> $tokenChecker->hasFrontendUser() ? FrontendUser::getInstance()->id : 0
	    			];

	    			Database::getInstance()->prepare("INSERT INTO tl_voting_results %s")->set($arrSet)->execute();
	            }

				// Redirect or reload the page
				$_SESSION['voting'][$this->obj->id] = $GLOBALS['TL_LANG']['MSC']['votingSubmitted'];

				$this->redirect($template->action);
			}
		}

		return $template->getResponse();
    }

	public function hasVoted(): bool
	{

		$intExpires = $this->obj->votingInterval ? (time() - $this->obj->votingInterval) : 0;

		// Check the cookie
		if (Input::cookie($this->Cookie.$this->obj->id) > $intExpires)
			return true;

		$tokenChecker = System::getContainer()->get('contao.security.token_checker');
		if ($this->obj->protected && $tokenChecker->hasFrontendUser())
            $objvoting = $this->db->prepare("SELECT * FROM tl_voting_results WHERE member=? AND ".
					    "tstamp >? AND pid IN (SELECT id FROM tl_voting_option WHERE pid=?".
            			(!$tokenChecker->hasBackendUser() ? " AND published=1" : "").") ORDER BY tstamp DESC")
            			->limit(1)
            			->execute(FrontendUser::getInstance()->id, $intExpires, $this->obj->id);
        else
    		$objvoting = $this->db->prepare("SELECT * FROM tl_voting_results WHERE ip=? AND ".
					    "tstamp >? AND pid IN (SELECT id FROM tl_voting_option WHERE pid=?".
    					(!$tokenChecker->hasBackendUser() ? " AND published=1" : "").") ORDER BY tstamp DESC")
    					->limit(1)
    					->execute(Environment::get('ip'), $intExpires, $this->obj->id);

		// User has already votingd
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

		$tokenChecker = System::getContainer()->get('contao.security.token_checker');
        switch ($strTable)
        {
		case 'tl_voting':
			$strQuery = "SELECT *, (SELECT COUNT(*) FROM tl_voting_option WHERE pid=tl_voting.id) AS ".
						"options FROM tl_voting WHERE id=?" . (!$tokenChecker->hasBackendUser() ? " AND published=1" : "");
			break;

		case 'tl_voting_option':
			$strQuery = "SELECT *, (SELECT COUNT(*) FROM tl_voting_results WHERE pid=tl_voting_option.id) AS ".
						"voting FROM tl_voting_option WHERE pid=?" . (!$tokenChecker->hasBackendUser() ? " AND published=1" : "").
						" ORDER BY sorting";
			break;
		}

		return $strQuery;
	}

}
