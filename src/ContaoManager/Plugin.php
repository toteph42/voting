<?php
declare(strict_types=1);

/*
 * 	This File is part of Toteph42 Voting bundle
 *
 *	@copyright	(c) Florian Daeumling, Germany. All right reserved
 * 	@license 	https://www.gnu.org/licenses/gpl-3.0.en.html
 */

namespace Toteph42\VotingBundle\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Toteph42\VotingBundle\VotingBundle;

class Plugin implements BundlePluginInterface
{

    public function getBundles(ParserInterface $parser): array
    {
        return [
            BundleConfig::create(VotingBundle::class)->setLoadAfter([ ContaoCoreBundle::class ]), ];
    }

}
