<?php

namespace App\Models\Logs;

use App\Models\Country;
use App\Models\Model;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * App\Models\Logs\LoginAttempt
 *
 * @property int $id
 * @property string $email
 * @property string $ip
 * @property string|null $country
 * @property int $successful
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \App\Helpers\QueryBuilderHelper|LoginAttempt newModelQuery()
 * @method static \App\Helpers\QueryBuilderHelper|LoginAttempt newQuery()
 * @method static \App\Helpers\QueryBuilderHelper|LoginAttempt page($page = 1, $columns = [])
 * @method static \App\Helpers\QueryBuilderHelper|LoginAttempt query()
 * @method static \App\Helpers\QueryBuilderHelper|LoginAttempt whereCountry($value)
 * @method static \App\Helpers\QueryBuilderHelper|LoginAttempt whereCreatedAt($value)
 * @method static \App\Helpers\QueryBuilderHelper|LoginAttempt whereEmail($value)
 * @method static \App\Helpers\QueryBuilderHelper|LoginAttempt whereId($value)
 * @method static \App\Helpers\QueryBuilderHelper|LoginAttempt whereIp($value)
 * @method static \App\Helpers\QueryBuilderHelper|LoginAttempt whereSuccessful($value)
 * @method static \App\Helpers\QueryBuilderHelper|LoginAttempt whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class LoginAttempt extends Model {
    protected $table = 'login_attempts';
    protected $fillable = ['email', 'ip', 'country', 'successful'];

    public static function log(string $email, string $ip, bool $successful): void {
        if (User::where('email', $email)->exists()) {

            DB::transaction(static function () use ($successful, $ip, $email) {
                if (LoginAttempt::where('email', $email)->count() === 10) {
                    LoginAttempt::where('email', $email)->first()->delete();
                }

                LoginAttempt::create([
                    'email' => $email,
                    'ip' => $ip,
                    'country' => ($ip=="127.0.0.1") ? 'US' : Country::getCodeByIp($ip)?->id,
                    'successful' => $successful
                ]);
            });

        }
    }
}
