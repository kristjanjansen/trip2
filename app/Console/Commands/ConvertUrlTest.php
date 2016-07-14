<?php

namespace App\Console\Commands;

use DB;

class ConvertUrlTest extends ConvertBase
{
    protected $signature = 'convert:urltest';

    public function handle()
    {

        $csv = array_map('str_getcsv', file(base_path().'/app/Console/Commands/ConvertUrlTest.csv'));

        array_walk($csv, function(&$a) use ($csv) {
          $a = array_combine($csv[0], $a);
        });
        array_shift($csv);

        $this->line(count($csv).' items');

        collect($csv)->slice(0, 10)->each(function($row) {

            $url = str_replace('http://trip.ee/', '', $row['URL']);

            $response = $this->client->head('http://localhost/'.$url, [
                'exceptions' => false,
            ]);

            $code = $response->getStatusCode();

            if (in_array($code, [200, 301])) {

                $this->info($row['Response Code'].' -> '.$code.' '.$url);
        
            } else {

            $this->error($row['Response Code'].' -> '.$code.' '.$url);

            }

        });

    }

};