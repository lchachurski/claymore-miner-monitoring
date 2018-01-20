<?php
require_once 'vendor/autoload.php';

$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

function connectDatabase() {
    $client = new InfluxDB\Client(getenv('INFLUXDB_HOST'), getenv('INFLUXDB_PORT'), getenv('INFLUXDB_USER'), getenv('INFLUXDB_PASS'));
    return $client->selectDB(getenv('INFLUXDB_DB'));
}

$database = connectDatabase();
$query     = '{"id":0,"jsonrpc":"2.0","method":"miner_getstat1"}';
while (true) {
    $minerConnectionString = sprintf('tcp://%s:%s', getenv('MINER_HOST'), getenv('MINER_PORT'));
    $client = new Hoa\Socket\Client($minerConnectionString);
    $client->connect();
    $client->writeString($query);
    $stats = json_decode($client->readLine());

    # no persistance at this moment
    $client->disconnect();

    if (! $stats instanceof StdClass) {
	sleep(1);
	continue;
    }

    $server = new Monitoring\Point\Server(getenv('MINER_NAME'), $stats);

    $serverPoints = [];
    $timestamp = time();

    foreach ($server->points as $name => $value) {
	$serverPoints[] = new InfluxDB\Point(
		$name, // name of the measurement
		$value, // the measurement value
		['host' => $server->name], // optional tags
		[],
		$timestamp // Time precision has to be set to seconds!
	);
    }

    $gpu = new Monitoring\Point\Gpu(getenv('MINER_NAME'), $stats);
    foreach ($gpu->points as $gpuId => $gpuData) {
        foreach ($gpuData as $name => $value) {
	    $gpuPoints[] = new InfluxDb\Point(
		$name,
		$value,
		['host' => $gpu->serverName, 'gpu' => $gpuId],
		[],
		$timestamp
	    );
	}
    }

    try {
        $result = $database->writePoints($serverPoints, InfluxDB\Database::PRECISION_SECONDS);
        $result = $database->writePoints($gpuPoints, InfluxDB\Database::PRECISION_SECONDS);

    } catch(\InfluxDB\Exception $e) {

        $database = connectDatabase();
    }

     sleep(1);
}
