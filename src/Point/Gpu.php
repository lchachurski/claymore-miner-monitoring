<?php
namespace Monitoring\Point;
use function Arrayy\create as a;

class Gpu {

    public $serverName;

    public $points = [
        'gpu_eth_hashrate' => null,
	'gpu_dcr_hashrate' => null,

	'gpu_temperature' => null,
	'gpu_fan_speed' => null
    ];

    public function __construct($serverName, \StdClass $data) {
        $this->serverName = $serverName;
	$stats = a((array)$data);

	$gpusEthHashRates = explode(';', $stats->get('result.3'));
	$gpusDcrHashRates = a(explode(';', $stats->get('result.5')));
	$gpusFanAndTemps = a(array_chunk(explode(';', $stats->get('result.6')), 2));

	$points =a([]);
	foreach($gpusEthHashRates as $gpu => $ethHashrate) {
	    $gpuData = [
	        'gpu_eth_hashrate' =>  (int) $ethHashrate,
	  	'gpu_dcr_hashrate' =>  (int) $gpusDcrHashRates->get($gpu),
		'gpu_temperature' =>  (int) $gpusFanAndTemps->get($gpu . '.' . 0),
		'gpu_fan_speed' => (int) $gpusFanAndTemps->get($gpu . '.' . 1)
	    ];

	    $points->set($gpu, $gpuData);
	}

        $this->points = $points->getArray();
   }
}
