<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class Domain implements Rule {
    public function __construct() {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value) {
        //https://stackoverflow.com/a/44029246/547185
        $regex = "/^";
        $regex .= "https\:\/\/"; // SCHEME Check
        //$regex .= "([a-z0-9+!*(),;?&=\$_.-]+(\:[a-z0-9+!*(),;?&=\$_.-]+)?@)?"; // User and Pass Check
        $regex .= "([a-z0-9-.]*)\.([a-z]+)"; // Host or IP Check
        //$regex .= "(\:[0-9]{2,5})?"; // Port Check
        $regex .= "(\/([a-z0-9+\$_-]\.?)+)*\/?"; // Path Check
        $regex .= "(\?[a-z+&\$_.-][a-z0-9;:@&%=+\/\$_.-]*)?"; // GET Query String Check
        $regex .= "(#[a-z_.-][a-z0-9+\$_.-]*)?"; // Anchor Check
        $regex .= "$/i";

        return preg_match($regex, $value) === 1;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message() {
        return 'The :attribute is not valid domain.';
    }

    public static function sanitizeDomain($domain): ?string {
        $domain = strtolower($domain);

        $pos = strpos($domain, "://");
        if ($pos !== false) {
            $domain = substr($domain, $pos + 3);
        }
        if (str_ends_with($domain, '/')) {
            $domain = substr($domain, 0, -1);
        }
        return !empty($domain) ? e('https://' . $domain) : null;
    }
}
