<?php

		return [

   			 'default' => env('QUEUE_CONNECTION', 'database'),

   			'connections' => [

                                'sync' => [
                                'driver' => 'sync',
                                ],

                                'database' => [
                                        'driver' => 'database',
                                        'table' => 'jobs',
                                        'queue' => 'default',
                                        'retry_after' => 90,
                                ],	

                        // Add other queue connections if needed
                        ],
    
                        'failed' => [
                            'driver' => env('QUEUE_FAILED_DRIVER', 'database-uuids'),
                            'database' => env('DB_CONNECTION', 'mysql'),
                            'table' => 'failed_jobs',
                        ],
    
            ];