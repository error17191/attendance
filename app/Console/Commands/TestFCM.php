<?php

namespace App\Console\Commands;

use GuzzleHttp\Client;
use Illuminate\Console\Command;

class TestFCM extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $client = new Client();
        $response = $client->post('https://fcm.googleapis.com/fcm/send', [
            'headers' => [
                'Authorization' => 'key=AAAAb-k3kcE:APA91bFsapb-_ia8k8VCl7eJg9yQTgncZD_FVIQxNLsy5OHqX5BPxgCYMYp88ux0GGSkTXlge-V42vrZLIZ0CmAWBAS1CceRIRqvKZVT1d5h-V_rvz2jda9xS6esh8e403xANWeXMW2E',
                'Content-Type' => 'application/json'
            ],
            'json' => [
                'notification' => [
                    'title' => 'Sba7 el zeft',
                    'body' => 'Yala bena',
                ],
                'to' => 'e-0d6dmMiaU:APA91bEqKshpn8UDeo7Mce2ZyuU5Yem3qcS4-9vbKScoKXcEj8GGji6XSPHEkYVk-_rb9ycGl2rktlRoqjH6cr7uvTRjBVkgWFs7uEVVvM7Ocql538ju33Sm1ZOUag33FWBF7bD3s4as'
            ]
        ]);

        echo $response->getBody()->getContents();
        echo PHP_EOL;
    }
}
