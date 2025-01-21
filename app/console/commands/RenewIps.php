<?php

namespace App\Console\Commands;

use DB;
use Exception;
use Illuminate\Console\Command;

/**
 * Class RenewIps
 * @package App\Console\Commands
 * Usage: php artisan ips:renew path/to/file.csv
 */
class RenewIps extends Command {
    protected $signature = 'ips:renew {csv}';

    protected $description = 'Populate countries_ips tables from DB-IP:ip-to-country lite CSV';

    public function handle() {
        ini_set("memory_limit", "-1");

        $csvContents = file_get_contents($this->argument('csv'));
        if ($csvContents === false) {
            $this->error("CSV not found.");
            return;
        }

        $conn = DB::connection();
        try {
            $conn->table("countries_ips_temp")->truncate();
            $conn->statement("START TRANSACTION;");

            $lines = explode("\n", $csvContents);
            $numLines = count($lines);
            $chunks = ceil($numLines / 10000);
            for ($p = 0; $p < $chunks; $p += 1) {
                $insert = [];
                $start = $p * 10000;
                for ($l = $start; $l < min($numLines, $start + 9999); $l += 1) {
                    $line = $lines[$l];
                    if (str_contains($line, ':')) {
                        break;
                    }
                    $cols = explode(",", $line);
                    $insert[] = ['start' => ip2long($cols[0]), 'end' => ip2long($cols[1]), 'country' => $cols[2]];
                    //$insert2[] = '(' . ip2long($cols[0]) . ',' . ip2long($cols[1]) . ',"' . $cols[2] . '")';
                }
                $result = $conn->table("countries_ips_temp")->insert($insert);
                if ($result) {
                    $this->info("Part#" . $p . " updated successfully.");
                } else {
                    throw new Exception("Operation failed.");
                }
            }

            $conn->statement("RENAME TABLE countries_ips TO countries_ips_old, countries_ips_temp TO countries_ips, countries_ips_old TO countries_ips_temp");
            $conn->commit();
        } catch (Exception $e) {
            $this->error("Operation failed: " . $e->getMessage() . ' ' . $e->getTraceAsString());
            $conn->rollBack();
        }
    }
}
