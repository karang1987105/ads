<?php

namespace App\Services\Currency;

use App\Models\Currency;
use App\Models\Payment;
use App\Models\User;
use App\Services\RPCClient;
use Exception;
use Http;
use Log;

class CurrencyService {
    protected Currency $currency;

    public function __construct(Currency $currency) {
        $this->currency = $currency;
    }

    protected function getRPCClient(): RPCClient {
        return new RPCClient($this->currency->rpc_server);
    }

    public function getWallet($label, bool $generateIfNotExists = true): string|null {
        $address = null;

        if (config('app.debug')) {
            return 'XXXXXXXXXXXXXXXXXXXX';
        }

        try {
            $client = $this->getRPCClient();

            try {
                $addresses = $client->{$this->command('getaddressesbylabel')}($label);
                if (is_array($addresses) && !empty($addresses)) {
                    $fkey = key($addresses);
                    if ($fkey === 0) { // Numeric array
                        $fval = $addresses[0];
                        if (is_string($fval)) {
                            /*
                             * CRW-like: ["WALLET_ADDRESS_1", "WALLET_ADDRESS_2", ...]
                             */
                            $address = $fval;
                        } elseif (is_array($fval)) { // JSON-Object
                            if (array_key_exists('address', $fval)) {
                                /*
                                 * BTC-like: [{"address":"WALLET_ADDRESS_1", ...}, "address":"WALLET_ADDRESS_2", ...}, ....]
                                 */
                                $address = $fval['address'];
                            } else {
                                Log::error("Unknown RPC response: " . json_encode($addresses));
                            }
                        } else {
                            Log::error("Unknown RPC response: " . json_encode($addresses));
                        }
                    } else {
                        /*
                         * LTC-like: ["WALLET_ADDRESS_1" => ...wallet info..., "WALLET_ADDRESS_2" => ...wallet info..., ...]
                         */
                        $address = $fkey;
                    }
                } elseif (is_string($addresses)) {
                    $address = $addresses;
                }
            } catch (Exception $e) {
                Log::error($e->getTraceAsString());
            }

            if ($address === null && $generateIfNotExists) {
                $address = $client->{$this->command('getnewaddress')}($label);
            }
        } catch (Exception $e) {
            Log::error($e->getTraceAsString());
        }
        return $address;
    }

    /**
     * @param $label
     * @param $amount float just for debug
     * @return array
     */
    protected function getTransactions($label, ?float $amount = null): array {
        $result = [];

        if (!config('app.debug')) {

            $transactions = $this->getRPCClient()->{$this->command('listtransactions')}($label);

        } else {
            $transactions = [
                [
                    "amount" => $amount,
                    "confirmations" => 30,
                    "txid" => "12959b06b3acee496d5941266003618755ce86d404a5c839370f67452eefaf88",
                    "timereceived" => time()
                ],
            ];
        }

        if (is_array($transactions)) {
            //Log::info("TX: " . json_encode($transactions));
            $now = time();
            $maxAge = config('ads.max_tx_age_for_confirmation') * 60;
            foreach ($transactions as $transaction) {
                $txTime = $transaction['timereceived'] ?? $transaction['time'];
                //Log::info("time check: " . "$now - $txTime <= $maxAge");
                if ($now - $txTime <= $maxAge) {
                    $result[$transaction['txid']] = $transaction;
                }
            }
        }

        if (!empty($result)) {
            $current = Payment::whereIn('txid', array_keys($result))->pluck('txid')->toArray();
            //Log::info("Existing TX: " . json_encode($current));
            $result = array_filter($result, fn($k) => !in_array($k, $current), ARRAY_FILTER_USE_KEY);
        }
        return $result;
    }

    public function verifyTransaction($label, float $amount): ?string {
        $minConfirmations = config('ads.min_tx_confirmations');
        $round = config('ads.crypto_decimal_places_length');
        $tolerance = round($amount * config('ads.max_tx_amount_tolerance_ratio'), $round);
        $amount = round($amount, $round);
        $transactions = $this->getTransactions($label, $amount);
        //Log::info("verify: " . json_encode(compact('minConfirmations', 'round', 'tolerance', 'amount', 'transactions')));
        foreach ($transactions as $txid => $transaction) {
            $verified = $transaction['confirmations'] >= $minConfirmations
                && $amount == $transaction['amount']
                && abs($amount - round($transaction['amount'], $round)) <= $tolerance;
            if ($verified) {
                return $txid;
            }
        }
        return null;
    }

    public function getLabel(User $user, array $options = []): string {
        return "$user->id";
    }

    public static function getExchangeRates(array|string $ids): array {
        $result = [];
        $ids = is_array($ids) ? join(',', $ids) : $ids;
        $response = Http::withOptions(config('ads.http_client_options'))->accept('application/json')
            ->get('https://api.coingecko.com/api/v3/simple/price', ['ids' => $ids, 'vs_currencies' => 'USD']);
        if ($response->successful()) {
            $json = $response->json();
            foreach ($json as $coingecko => $value) {
                $result[$coingecko] = $json[$coingecko]['usd'];
            }
        }
        return $result;
    }

    public function getBlockCount(): int {
        $command = $this->command('getblockcount');
        return $this->getRPCClient()->{$command}();
    }

    /**
     * @param string('getaddressesbylabel', 'getnewaddress', 'listtransactions', 'getblockcount') $command
     * @return string
     */
    protected function command(string $command): string {
        $config = config('ads.daemon.coins');
        return isset($config[$this->currency->id], $config[$this->currency->id][$command]) ? $config[$this->currency->id][$command] : $command;
    }
}