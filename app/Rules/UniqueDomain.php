<?php

namespace App\Rules;

use App\Helpers\Helper;
use DB;
use Illuminate\Contracts\Validation\Rule;

class UniqueDomain implements Rule {

    private string $table;
    private ?string $column = null;
    private ?int $ignore = null;

    public function __construct(string $table, ?string $column = null, ?int $ignore = null) {
        $this->table = $table;
        $this->column = $column;
        $this->ignore = $ignore;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value) {
        $domain = preg_quote(Helper::getUrlDomain($value), '');
        $query = DB::table($this->table)
            ->where($this->column ?? $attribute, 'REGEXP', 'https\:\/\/([a-z0-9-.]+\.)*' . $domain . '(\/.)*')
            ->when($this->table === 'users_advertisers_domains', function ($q) {
                $q->when(
                    auth()->user()->isAdmin(),
                    fn($q) => $q->whereNull('advertiser_id'),
                    fn($q) => $q->whereNotNull('advertiser_id')
                );
            });
        //$query->dump();
        if ($this->ignore) {
            $query->where('id', '!=', $this->ignore);
        }
        return !$query->exists();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message() {
        return 'This :attribute exists already!';
    }
}
