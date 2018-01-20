<?php
namespace Monitoring\Point;
use function Arrayy\create as a;

class Server {

    public $name;

    public $points = [
        'server_uptime' => null,

	'server_eth_hashrate' => null,
	'server_eth_accepted_shares' => null,
	'server_eth_rejected_shares' => null,
	'server_eth_invalid_shares' => null,

	'server_dcr_hashrate' => null,
	'server_dcr_accepted_shares' => null,
	'server_dcr_rejected_shares' => null,
	'server_dcr_invalid_shares'  => null
    ];

    public function __construct($name, \StdClass $data) {
        $this->name = $name;
        $stats = a((array)$data);

        list($ethHashrate, $ethAcceptedShares, $ethRejectedShares) = explode(';', $stats->get('result.2'));
        list($dcrHashrate, $dcrAcceptedShares, $dcrRejectedShares) = explode(';', $stats->get('result.4'));
        list($ethInvalidShares, $ethPoolSwitches, $dcrInvalidShares, $dcrPoolSwitches) = explode(';', $stats->get('result.8'));

	$points = a($this->points);
	$points->set('server_uptime', (int) $stats->get('result.1'));

        $points->set('server_eth_hashrate', (int) $ethHashrate);
        $points->set('server_eth_accepted_shares', (int) $ethAcceptedShares);
        $points->set('server_eth_rejected_shares', (int) $ethRejectedShares);
        $points->set('server_eth_invalid_shares', (int) $ethInvalidShares);

        $points->set('server_dcr_hashrate', (int) $dcrHashrate);
        $points->set('server_dcr_accepted_shares', (int) $dcrAcceptedShares);
        $points->set('server_dcr_rejected_shares', (int) $dcrRejectedShares);
        $points->set('server_dcr_invalid_shares', (int) $dcrInvalidShares);

        $this->points = $points->getArray();
   }
}
