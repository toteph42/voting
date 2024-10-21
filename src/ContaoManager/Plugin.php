<?php
declare(strict_types=1);

/*
 * 	Voting Bundle
 *
 *	@copyright	(c) 2023 - 2024 Florian Daeumling, Germany. All right reserved
 * 	@license 	https://github.com/toteph42/voting/blob/master/LICENSE
 */

namespace Toteph42\VotingBundle\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Toteph42\VotingBundle\VotingBundle;

class Plugin implements BundlePluginInterface {

    public function getBundles(ParserInterface $parser): array {

        return [
            BundleConfig::create(VotingBundle::class)
                ->setLoadAfter([ ContaoCoreBundle::class ]), ];
    }

}
