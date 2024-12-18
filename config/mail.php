<?php
	return [
   		  'driver' => env('MAIL_DRIVER', 'smtp'),
  		  'host' => env('MAIL_HOST', 'smtp.mailtrap.io'),
		  'port' => env('MAIL_PORT', 2525),
  		  'from' => [
        		 	 'address' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
       		 	 'name' => env('MAIL_FROM_NAME', 'Example'),
  		  ],
   		  'encryption' => env('MAIL_ENCRYPTION', 'tls'),
  		  'username' => env('MAIL_USERNAME'),
   		  'password' => env('MAIL_PASSWORD'),
	];