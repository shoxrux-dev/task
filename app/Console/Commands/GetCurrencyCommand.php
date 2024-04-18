<?php

namespace App\Console\Commands;

use App\Models\Currency;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class GetCurrencyCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:currency';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get currencies using API';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://openexchangerates.org/api/currencies.json?prettyprint=false&show_alternative=false&show_inactive=false&app_id=1");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            $this->error('Curl error: ' . curl_error($ch));
            return 1;
        }

        curl_close($ch);

        $currencies = json_decode($response, true);

        info($currencies);

        if ($currencies) {
            foreach ($currencies as $code => $name) {
                Currency::updateOrCreate(
                    ['name' => $name],
                    ['code' => $code]
                );
            }
            $this->info('Currencies fetched and stored successfully.');
        } else {
            $this->error('Failed to fetch currencies.');
            return 1;
        }

        return 0;
    }

}
