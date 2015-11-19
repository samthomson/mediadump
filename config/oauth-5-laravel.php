<?php

return [

	/*
	|--------------------------------------------------------------------------
	| oAuth Config
	|--------------------------------------------------------------------------
	*/

	/**
	 * Storage
	 */
	'storage' => 'Session',

	/**
	 * Consumers
	 */
	'consumers' => [

		'Facebook' => [
			'client_id'     => '',
			'client_secret' => '',
			'scope'         => [],
		],

		'DropBox' => [
			'client_id'     => 'cfjgyp5s0cq1eu7',
			'client_secret' => 'm9vn3p05dmes903',
			'scope'         => [],
		],

	]

];