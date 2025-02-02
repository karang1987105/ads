<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CountrySeeder extends Seeder {

    private static function tier() {
        return ['Tier 1', 'Tier 2', 'Tier 3', 'Tier 4'][min(rand(0, 7), 3)];
    }

    private static function getList() {
        return [
            ['id' => 'AF', 'name' => 'Afghanistan', 'category' => self::tier(), 'utc_start' => 270],
            ['id' => 'AX', 'name' => 'Aland Islands', 'category' => self::tier(), 'utc_start' => 120],
            ['id' => 'AL', 'name' => 'Albania', 'category' => self::tier(), 'utc_start' => 60],
            ['id' => 'DZ', 'name' => 'Algeria', 'category' => self::tier(), 'utc_start' => 60],
            ['id' => 'AS', 'name' => 'American Samoa', 'category' => self::tier(), 'utc_start' => -660],
            ['id' => 'AD', 'name' => 'Andorra', 'category' => self::tier(), 'utc_start' => 60],
            ['id' => 'AO', 'name' => 'Angola', 'category' => self::tier(), 'utc_start' => 60],
            ['id' => 'AI', 'name' => 'Anguilla', 'category' => self::tier(), 'utc_start' => -240],
            ['id' => 'AQ', 'name' => 'Antarctica', 'category' => self::tier(), 'utc_start' => -180],
            ['id' => 'AG', 'name' => 'Antigua and Barbuda', 'category' => self::tier(), 'utc_start' => -240],
            ['id' => 'AN', 'name' => 'Antilles', 'category' => self::tier(), 'utc_start' => -240],
            ['id' => 'AR', 'name' => 'Argentina', 'category' => self::tier(), 'utc_start' => -180],
            ['id' => 'AM', 'name' => 'Armenia', 'category' => self::tier(), 'utc_start' => 240],
            ['id' => 'AW', 'name' => 'Aruba', 'category' => self::tier(), 'utc_start' => -240],
            ['id' => 'AU', 'name' => 'Australia', 'category' => self::tier(), 'utc_start' => 480],
            ['id' => 'AT', 'name' => 'Austria', 'category' => self::tier(), 'utc_start' => 60],
            ['id' => 'AZ', 'name' => 'Azerbaijan', 'category' => self::tier(), 'utc_start' => 240],
            ['id' => 'BS', 'name' => 'Bahamas', 'category' => self::tier(), 'utc_start' => -300],
            ['id' => 'BH', 'name' => 'Bahrain', 'category' => self::tier(), 'utc_start' => 180],
            ['id' => 'BD', 'name' => 'Bangladesh', 'category' => self::tier(), 'utc_start' => 360],
            ['id' => 'BB', 'name' => 'Barbados', 'category' => self::tier(), 'utc_start' => -240],
            ['id' => 'BY', 'name' => 'Belarus', 'category' => self::tier(), 'utc_start' => 180],
            ['id' => 'BE', 'name' => 'Belgium', 'category' => self::tier(), 'utc_start' => 60],
            ['id' => 'BZ', 'name' => 'Belize', 'category' => self::tier(), 'utc_start' => -360],
            ['id' => 'BJ', 'name' => 'Benin', 'category' => self::tier(), 'utc_start' => 60],
            ['id' => 'BM', 'name' => 'Bermuda', 'category' => self::tier(), 'utc_start' => -240],
            ['id' => 'BT', 'name' => 'Bhutan', 'category' => self::tier(), 'utc_start' => 360],
            ['id' => 'BO', 'name' => 'Bolivia', 'category' => self::tier(), 'utc_start' => -240],
            ['id' => 'BQ', 'name' => 'Bonaire, Sint Eustatius and Saba', 'category' => self::tier(), 'utc_start' => -240],
            ['id' => 'BA', 'name' => 'Bosnia and Herzegovina', 'category' => self::tier(), 'utc_start' => 60],
            ['id' => 'BW', 'name' => 'Botswana', 'category' => self::tier(), 'utc_start' => 120],
            ['id' => 'BV', 'name' => 'Bouvet Island', 'category' => self::tier(), 'utc_start' => 1000000],
            ['id' => 'BR', 'name' => 'Brazil', 'category' => self::tier(), 'utc_start' => -300],
            ['id' => 'IO', 'name' => 'British Indian Ocean Territory', 'category' => self::tier(), 'utc_start' => 360],
            ['id' => 'VG', 'name' => 'British Virgin Islands', 'category' => self::tier(), 'utc_start' => -240],
            ['id' => 'BN', 'name' => 'Brunei', 'category' => self::tier(), 'utc_start' => 480],
            ['id' => 'BG', 'name' => 'Bulgaria', 'category' => self::tier(), 'utc_start' => 120],
            ['id' => 'BF', 'name' => 'Burkina Faso', 'category' => self::tier(), 'utc_start' => 0],
            ['id' => 'BI', 'name' => 'Burundi', 'category' => self::tier(), 'utc_start' => 120],
            ['id' => 'KH', 'name' => 'Cambodia', 'category' => self::tier(), 'utc_start' => 420],
            ['id' => 'CM', 'name' => 'Cameroon', 'category' => self::tier(), 'utc_start' => 60],
            ['id' => 'CA', 'name' => 'Canada', 'category' => self::tier(), 'utc_start' => -480],
            ['id' => 'CV', 'name' => 'Cape Verde', 'category' => self::tier(), 'utc_start' => -60],
            ['id' => 'KY', 'name' => 'Cayman Islands', 'category' => self::tier(), 'utc_start' => -300],
            ['id' => 'CF', 'name' => 'Central African Republic', 'category' => self::tier(), 'utc_start' => 60],
            ['id' => 'TD', 'name' => 'Chad', 'category' => self::tier(), 'utc_start' => 60],
            ['id' => 'CL', 'name' => 'Chile', 'category' => self::tier(), 'utc_start' => -360],
            ['id' => 'CN', 'name' => 'China', 'category' => self::tier(), 'utc_start' => 360],
            ['id' => 'CX', 'name' => 'Christmas Island', 'category' => self::tier(), 'utc_start' => 420],
            ['id' => 'CC', 'name' => 'Cocos Islands', 'category' => self::tier(), 'utc_start' => 390],
            ['id' => 'CO', 'name' => 'Colombia', 'category' => self::tier(), 'utc_start' => -300],
            ['id' => 'KM', 'name' => 'Comoros', 'category' => self::tier(), 'utc_start' => 180],
            ['id' => 'CG', 'name' => 'Congo', 'category' => self::tier(), 'utc_start' => 60],
            ['id' => 'CK', 'name' => 'Cook Islands', 'category' => self::tier(), 'utc_start' => -600],
            ['id' => 'CR', 'name' => 'Costa Rica', 'category' => self::tier(), 'utc_start' => -360],
            ['id' => 'CI', 'name' => "Cote d'Ivoire", 'category' => self::tier(), 'utc_start' => 0],
            ['id' => 'HR', 'name' => 'Croatia', 'category' => self::tier(), 'utc_start' => 60],
            ['id' => 'CU', 'name' => 'Cuba', 'category' => self::tier(), 'utc_start' => -300],
            ['id' => 'CW', 'name' => 'Curacao', 'category' => self::tier(), 'utc_start' => -240],
            ['id' => 'CY', 'name' => 'Cyprus', 'category' => self::tier(), 'utc_start' => 120],
            ['id' => 'CZ', 'name' => 'Czech Republic', 'category' => self::tier(), 'utc_start' => 60],
            ['id' => 'CD', 'name' => 'Democratic Republic of the Congo', 'category' => self::tier(), 'utc_start' => 60],
            ['id' => 'DK', 'name' => 'Denmark', 'category' => self::tier(), 'utc_start' => 60],
            ['id' => 'DJ', 'name' => 'Djibouti', 'category' => self::tier(), 'utc_start' => 180],
            ['id' => 'DM', 'name' => 'Dominica', 'category' => self::tier(), 'utc_start' => -240],
            ['id' => 'DO', 'name' => 'Dominican Republic', 'category' => self::tier(), 'utc_start' => -240],
            ['id' => 'EC', 'name' => 'Ecuador', 'category' => self::tier(), 'utc_start' => -360],
            ['id' => 'EG', 'name' => 'Egypt', 'category' => self::tier(), 'utc_start' => 120],
            ['id' => 'SV', 'name' => 'El Salvador', 'category' => self::tier(), 'utc_start' => -360],
            ['id' => 'GQ', 'name' => 'Equatorial Guinea', 'category' => self::tier(), 'utc_start' => 60],
            ['id' => 'ER', 'name' => 'Eritrea', 'category' => self::tier(), 'utc_start' => 180],
            ['id' => 'EE', 'name' => 'Estonia', 'category' => self::tier(), 'utc_start' => 120],
            ['id' => 'ET', 'name' => 'Ethiopia', 'category' => self::tier(), 'utc_start' => 180],
            ['id' => 'FK', 'name' => 'Falkland Islands', 'category' => self::tier(), 'utc_start' => -180],
            ['id' => 'FO', 'name' => 'Faroe Islands', 'category' => self::tier(), 'utc_start' => 0],
            ['id' => 'FJ', 'name' => 'Fiji', 'category' => self::tier(), 'utc_start' => 720],
            ['id' => 'FI', 'name' => 'Finland', 'category' => self::tier(), 'utc_start' => 120],
            ['id' => 'FR', 'name' => 'France', 'category' => self::tier(), 'utc_start' => 60],
            ['id' => 'GF', 'name' => 'French Guiana', 'category' => self::tier(), 'utc_start' => -180],
            ['id' => 'PF', 'name' => 'French Polynesia', 'category' => self::tier(), 'utc_start' => -600],
            ['id' => 'TF', 'name' => 'French Southern and Antarctic Territories', 'category' => self::tier(), 'utc_start' => 300],
            ['id' => 'GA', 'name' => 'Gabon', 'category' => self::tier(), 'utc_start' => 60],
            ['id' => 'GM', 'name' => 'Gambia', 'category' => self::tier(), 'utc_start' => 0],
            ['id' => 'GE', 'name' => 'Georgia', 'category' => self::tier(), 'utc_start' => 240],
            ['id' => 'DE', 'name' => 'Germany', 'category' => self::tier(), 'utc_start' => 60],
            ['id' => 'GH', 'name' => 'Ghana', 'category' => self::tier(), 'utc_start' => 0],
            ['id' => 'GI', 'name' => 'Gibraltar', 'category' => self::tier(), 'utc_start' => 60],
            ['id' => 'GR', 'name' => 'Greece', 'category' => self::tier(), 'utc_start' => 120],
            ['id' => 'GL', 'name' => 'Greenland', 'category' => self::tier(), 'utc_start' => -240],
            ['id' => 'GD', 'name' => 'Grenada', 'category' => self::tier(), 'utc_start' => -240],
            ['id' => 'GP', 'name' => 'Guadeloupe', 'category' => self::tier(), 'utc_start' => -240],
            ['id' => 'GU', 'name' => 'Guam', 'category' => self::tier(), 'utc_start' => 600],
            ['id' => 'GT', 'name' => 'Guatemala', 'category' => self::tier(), 'utc_start' => -360],
            ['id' => 'GG', 'name' => 'Guernsey', 'category' => self::tier(), 'utc_start' => 0],
            ['id' => 'GN', 'name' => 'Guinea', 'category' => self::tier(), 'utc_start' => 0],
            ['id' => 'GW', 'name' => 'Guinea-Bissau', 'category' => self::tier(), 'utc_start' => 0],
            ['id' => 'GY', 'name' => 'Guyana', 'category' => self::tier(), 'utc_start' => -240],
            ['id' => 'HT', 'name' => 'Haiti', 'category' => self::tier(), 'utc_start' => -300],
            ['id' => 'HM', 'name' => 'Heard and McDonald Islands', 'category' => self::tier(), 'utc_start' => 1000000],
            ['id' => 'HN', 'name' => 'Honduras', 'category' => self::tier(), 'utc_start' => -360],
            ['id' => 'HK', 'name' => 'Hong Kong', 'category' => self::tier(), 'utc_start' => 480],
            ['id' => 'HU', 'name' => 'Hungary', 'category' => self::tier(), 'utc_start' => 60],
            ['id' => 'IS', 'name' => 'Iceland', 'category' => self::tier(), 'utc_start' => 0],
            ['id' => 'IN', 'name' => 'India', 'category' => self::tier(), 'utc_start' => 330],
            ['id' => 'ID', 'name' => 'Indonesia', 'category' => self::tier(), 'utc_start' => 420],
            ['id' => 'IR', 'name' => 'Iran', 'category' => self::tier(), 'utc_start' => 210],
            ['id' => 'IQ', 'name' => 'Iraq', 'category' => self::tier(), 'utc_start' => 180],
            ['id' => 'IE', 'name' => 'Ireland', 'category' => self::tier(), 'utc_start' => 0],
            ['id' => 'IM', 'name' => 'Isle of Man', 'category' => self::tier(), 'utc_start' => 0],
            ['id' => 'IL', 'name' => 'Israel', 'category' => self::tier(), 'utc_start' => 120],
            ['id' => 'IT', 'name' => 'Italy', 'category' => self::tier(), 'utc_start' => 60],
            ['id' => 'JM', 'name' => 'Jamaica', 'category' => self::tier(), 'utc_start' => -300],
            ['id' => 'JP', 'name' => 'Japan', 'category' => self::tier(), 'utc_start' => 540],
            ['id' => 'JE', 'name' => 'Jersey', 'category' => self::tier(), 'utc_start' => 0],
            ['id' => 'JO', 'name' => 'Jordan', 'category' => self::tier(), 'utc_start' => 120],
            ['id' => 'KZ', 'name' => 'Kazakhstan', 'category' => self::tier(), 'utc_start' => 300],
            ['id' => 'KE', 'name' => 'Kenya', 'category' => self::tier(), 'utc_start' => 180],
            ['id' => 'KI', 'name' => 'Kiribati', 'category' => self::tier(), 'utc_start' => 720],
            ['id' => 'XK', 'name' => 'Kosovo', 'category' => self::tier(), 'utc_start' => -120],
            ['id' => 'KW', 'name' => 'Kuwait', 'category' => self::tier(), 'utc_start' => 180],
            ['id' => 'KG', 'name' => 'Kyrgyzstan', 'category' => self::tier(), 'utc_start' => 360],
            ['id' => 'LA', 'name' => 'Laos', 'category' => self::tier(), 'utc_start' => 420],
            ['id' => 'LV', 'name' => 'Latvia', 'category' => self::tier(), 'utc_start' => 120],
            ['id' => 'LB', 'name' => 'Lebanon', 'category' => self::tier(), 'utc_start' => 120],
            ['id' => 'LS', 'name' => 'Lesotho', 'category' => self::tier(), 'utc_start' => 120],
            ['id' => 'LR', 'name' => 'Liberia', 'category' => self::tier(), 'utc_start' => 0],
            ['id' => 'LY', 'name' => 'Libya', 'category' => self::tier(), 'utc_start' => 120],
            ['id' => 'LI', 'name' => 'Liechtenstein', 'category' => self::tier(), 'utc_start' => 60],
            ['id' => 'LT', 'name' => 'Lithuania', 'category' => self::tier(), 'utc_start' => 120],
            ['id' => 'LU', 'name' => 'Luxembourg', 'category' => self::tier(), 'utc_start' => 60],
            ['id' => 'MO', 'name' => 'Macau', 'category' => self::tier(), 'utc_start' => 480],
            ['id' => 'MK', 'name' => 'Macedonia', 'category' => self::tier(), 'utc_start' => 60],
            ['id' => 'MG', 'name' => 'Madagascar', 'category' => self::tier(), 'utc_start' => 180],
            ['id' => 'MW', 'name' => 'Malawi', 'category' => self::tier(), 'utc_start' => 120],
            ['id' => 'MY', 'name' => 'Malaysia', 'category' => self::tier(), 'utc_start' => 480],
            ['id' => 'MV', 'name' => 'Maldives', 'category' => self::tier(), 'utc_start' => 300],
            ['id' => 'ML', 'name' => 'Mali', 'category' => self::tier(), 'utc_start' => 0],
            ['id' => 'MT', 'name' => 'Malta', 'category' => self::tier(), 'utc_start' => 60],
            ['id' => 'MH', 'name' => 'Marshall Islands', 'category' => self::tier(), 'utc_start' => 720],
            ['id' => 'MQ', 'name' => 'Martinique', 'category' => self::tier(), 'utc_start' => -240],
            ['id' => 'MR', 'name' => 'Mauritania', 'category' => self::tier(), 'utc_start' => 0],
            ['id' => 'MU', 'name' => 'Mauritius', 'category' => self::tier(), 'utc_start' => 240],
            ['id' => 'YT', 'name' => 'Mayotte', 'category' => self::tier(), 'utc_start' => 180],
            ['id' => 'MX', 'name' => 'Mexico', 'category' => self::tier(), 'utc_start' => -480],
            ['id' => 'FM', 'name' => 'Micronesia', 'category' => self::tier(), 'utc_start' => 600],
            ['id' => 'MD', 'name' => 'Moldova', 'category' => self::tier(), 'utc_start' => 120],
            ['id' => 'MC', 'name' => 'Monaco', 'category' => self::tier(), 'utc_start' => 60],
            ['id' => 'MN', 'name' => 'Mongolia', 'category' => self::tier(), 'utc_start' => 420],
            ['id' => 'ME', 'name' => 'Montenegro', 'category' => self::tier(), 'utc_start' => 60],
            ['id' => 'MS', 'name' => 'Montserrat', 'category' => self::tier(), 'utc_start' => -240],
            ['id' => 'MA', 'name' => 'Morocco', 'category' => self::tier(), 'utc_start' => 0],
            ['id' => 'MZ', 'name' => 'Mozambique', 'category' => self::tier(), 'utc_start' => 120],
            ['id' => 'MM', 'name' => 'Myanmar', 'category' => self::tier(), 'utc_start' => 390],
            ['id' => 'NA', 'name' => 'Namibia', 'category' => self::tier(), 'utc_start' => 120],
            ['id' => 'NR', 'name' => 'Nauru', 'category' => self::tier(), 'utc_start' => 720],
            ['id' => 'NP', 'name' => 'Nepal', 'category' => self::tier(), 'utc_start' => 345],
            ['id' => 'NL', 'name' => 'Netherlands', 'category' => self::tier(), 'utc_start' => 60],
            ['id' => 'NC', 'name' => 'New Caledonia', 'category' => self::tier(), 'utc_start' => 660],
            ['id' => 'NZ', 'name' => 'New Zealand', 'category' => self::tier(), 'utc_start' => 720],
            ['id' => 'NI', 'name' => 'Nicaragua', 'category' => self::tier(), 'utc_start' => -360],
            ['id' => 'NE', 'name' => 'Niger', 'category' => self::tier(), 'utc_start' => 60],
            ['id' => 'NG', 'name' => 'Nigeria', 'category' => self::tier(), 'utc_start' => 60],
            ['id' => 'NU', 'name' => 'Niue', 'category' => self::tier(), 'utc_start' => -660],
            ['id' => 'NF', 'name' => 'Norfolk Island', 'category' => self::tier(), 'utc_start' => 660],
            ['id' => 'KP', 'name' => 'North Korea', 'category' => self::tier(), 'utc_start' => 540],
            ['id' => 'MP', 'name' => 'Northern Mariana Islands', 'category' => self::tier(), 'utc_start' => 600],
            ['id' => 'NO', 'name' => 'Norway', 'category' => self::tier(), 'utc_start' => 60],
            ['id' => 'OM', 'name' => 'Oman', 'category' => self::tier(), 'utc_start' => 240],
            ['id' => 'PK', 'name' => 'Pakistan', 'category' => self::tier(), 'utc_start' => 300],
            ['id' => 'PW', 'name' => 'Palau', 'category' => self::tier(), 'utc_start' => 540],
            ['id' => 'PS', 'name' => 'Palestine', 'category' => self::tier(), 'utc_start' => 120],
            ['id' => 'PA', 'name' => 'Panama', 'category' => self::tier(), 'utc_start' => -300],
            ['id' => 'PG', 'name' => 'Papua New Guinea', 'category' => self::tier(), 'utc_start' => 600],
            ['id' => 'PY', 'name' => 'Paraguay', 'category' => self::tier(), 'utc_start' => -240],
            ['id' => 'PE', 'name' => 'Peru', 'category' => self::tier(), 'utc_start' => -300],
            ['id' => 'PH', 'name' => 'Philippines', 'category' => self::tier(), 'utc_start' => 480],
            ['id' => 'PN', 'name' => 'Pitcairn Island', 'category' => self::tier(), 'utc_start' => -480],
            ['id' => 'PL', 'name' => 'Poland', 'category' => self::tier(), 'utc_start' => 60],
            ['id' => 'PT', 'name' => 'Portugal', 'category' => self::tier(), 'utc_start' => -60],
            ['id' => 'PR', 'name' => 'Puerto Rico', 'category' => self::tier(), 'utc_start' => -240],
            ['id' => 'QA', 'name' => 'Qatar', 'category' => self::tier(), 'utc_start' => 180],
            ['id' => 'RE', 'name' => 'Reunion', 'category' => self::tier(), 'utc_start' => 240],
            ['id' => 'RO', 'name' => 'Romania', 'category' => self::tier(), 'utc_start' => 120],
            ['id' => 'RU', 'name' => 'Russia', 'category' => self::tier(), 'utc_start' => 120],
            ['id' => 'RW', 'name' => 'Rwanda', 'category' => self::tier(), 'utc_start' => 120],
            ['id' => 'BL', 'name' => 'Saint Barthelemy', 'category' => self::tier(), 'utc_start' => -240],
            ['id' => 'SH', 'name' => 'Saint Helena', 'category' => self::tier(), 'utc_start' => 0],
            ['id' => 'KN', 'name' => 'Saint Kitts and Nevis Anguilla', 'category' => self::tier(), 'utc_start' => -240],
            ['id' => 'LC', 'name' => 'Saint Lucia', 'category' => self::tier(), 'utc_start' => -240],
            ['id' => 'MF', 'name' => 'Saint Martin', 'category' => self::tier(), 'utc_start' => -240],
            ['id' => 'PM', 'name' => 'Saint Pierre and Miquelon', 'category' => self::tier(), 'utc_start' => -180],
            ['id' => 'VC', 'name' => 'Saint Vincent and the Grenadines', 'category' => self::tier(), 'utc_start' => -240],
            ['id' => 'WS', 'name' => 'Samoa', 'category' => self::tier(), 'utc_start' => 780],
            ['id' => 'SM', 'name' => 'San Marino', 'category' => self::tier(), 'utc_start' => 60],
            ['id' => 'ST', 'name' => 'Sao Tome and Principe', 'category' => self::tier(), 'utc_start' => 0],
            ['id' => 'SA', 'name' => 'Saudi Arabia', 'category' => self::tier(), 'utc_start' => 180],
            ['id' => 'SN', 'name' => 'Senegal', 'category' => self::tier(), 'utc_start' => 0],
            ['id' => 'RS', 'name' => 'Serbia', 'category' => self::tier(), 'utc_start' => 60],
            ['id' => 'SC', 'name' => 'Seychelles', 'category' => self::tier(), 'utc_start' => 240],
            ['id' => 'SL', 'name' => 'Sierra Leone', 'category' => self::tier(), 'utc_start' => 0],
            ['id' => 'SG', 'name' => 'Singapore', 'category' => self::tier(), 'utc_start' => 480],
            ['id' => 'SX', 'name' => 'Sint Maarten', 'category' => self::tier(), 'utc_start' => -240],
            ['id' => 'SK', 'name' => 'Slovakia', 'category' => self::tier(), 'utc_start' => 60],
            ['id' => 'SI', 'name' => 'Slovenia', 'category' => self::tier(), 'utc_start' => 60],
            ['id' => 'SB', 'name' => 'Solomon Islands', 'category' => self::tier(), 'utc_start' => 660],
            ['id' => 'SO', 'name' => 'Somalia', 'category' => self::tier(), 'utc_start' => 180],
            ['id' => 'ZA', 'name' => 'South Africa', 'category' => self::tier(), 'utc_start' => 120],
            ['id' => 'KR', 'name' => 'South Korea', 'category' => self::tier(), 'utc_start' => 540],
            ['id' => 'SS', 'name' => 'South Sudan', 'category' => self::tier(), 'utc_start' => 120],
            ['id' => 'ES', 'name' => 'Spain', 'category' => self::tier(), 'utc_start' => 0],
            ['id' => 'LK', 'name' => 'Sri Lanka', 'category' => self::tier(), 'utc_start' => 330],
            ['id' => 'SD', 'name' => 'Sudan', 'category' => self::tier(), 'utc_start' => 120],
            ['id' => 'SR', 'name' => 'Suriname', 'category' => self::tier(), 'utc_start' => -180],
            ['id' => 'SJ', 'name' => 'Svalbard and Jan Mayen', 'category' => self::tier(), 'utc_start' => 60],
            ['id' => 'SZ', 'name' => 'Swaziland', 'category' => self::tier(), 'utc_start' => 120],
            ['id' => 'SE', 'name' => 'Sweden', 'category' => self::tier(), 'utc_start' => 60],
            ['id' => 'CH', 'name' => 'Switzerland', 'category' => self::tier(), 'utc_start' => 60],
            ['id' => 'SY', 'name' => 'Syria', 'category' => self::tier(), 'utc_start' => 120],
            ['id' => 'TW', 'name' => 'Taiwan', 'category' => self::tier(), 'utc_start' => 480],
            ['id' => 'TJ', 'name' => 'Tajikistan', 'category' => self::tier(), 'utc_start' => 300],
            ['id' => 'TZ', 'name' => 'Tanzania', 'category' => self::tier(), 'utc_start' => 180],
            ['id' => 'TH', 'name' => 'Thailand', 'category' => self::tier(), 'utc_start' => 420],
            ['id' => 'TL', 'name' => 'Timor Leste', 'category' => self::tier(), 'utc_start' => 540],
            ['id' => 'TG', 'name' => 'Togo', 'category' => self::tier(), 'utc_start' => 0],
            ['id' => 'TK', 'name' => 'Tokelau', 'category' => self::tier(), 'utc_start' => 780],
            ['id' => 'TO', 'name' => 'Tonga', 'category' => self::tier(), 'utc_start' => 780],
            ['id' => 'TT', 'name' => 'Trinidad and Tobago', 'category' => self::tier(), 'utc_start' => -240],
            ['id' => 'TN', 'name' => 'Tunisia', 'category' => self::tier(), 'utc_start' => 60],
            ['id' => 'TR', 'name' => 'Turkey', 'category' => self::tier(), 'utc_start' => 180],
            ['id' => 'TM', 'name' => 'Turkmenistan', 'category' => self::tier(), 'utc_start' => 300],
            ['id' => 'TC', 'name' => 'Turks and Caicos', 'category' => self::tier(), 'utc_start' => -300],
            ['id' => 'TV', 'name' => 'Tuvalu', 'category' => self::tier(), 'utc_start' => 720],
            ['id' => 'VI', 'name' => 'US Virgin Islands', 'category' => self::tier(), 'utc_start' => -240],
            ['id' => 'UG', 'name' => 'Uganda', 'category' => self::tier(), 'utc_start' => 180],
            ['id' => 'UA', 'name' => 'Ukraine', 'category' => self::tier(), 'utc_start' => 120],
            ['id' => 'AE', 'name' => 'United Arab Emirates', 'category' => self::tier(), 'utc_start' => 240],
            ['id' => 'GB', 'name' => 'United Kingdom', 'category' => self::tier(), 'utc_start' => 0],
            ['id' => 'US', 'name' => 'United States', 'category' => self::tier(), 'utc_start' => -600],
            ['id' => 'UM', 'name' => 'United States Minor Outlying Islands', 'category' => self::tier(), 'utc_start' => -660],
            ['id' => 'UY', 'name' => 'Uruguay', 'category' => self::tier(), 'utc_start' => -180],
            ['id' => 'UZ', 'name' => 'Uzbekistan', 'category' => self::tier(), 'utc_start' => 300],
            ['id' => 'VU', 'name' => 'Vanuatu', 'category' => self::tier(), 'utc_start' => 660],
            ['id' => 'VA', 'name' => 'Vatican City', 'category' => self::tier(), 'utc_start' => 60],
            ['id' => 'VE', 'name' => 'Venezuela', 'category' => self::tier(), 'utc_start' => -240],
            ['id' => 'VN', 'name' => 'Vietnam', 'category' => self::tier(), 'utc_start' => 420],
            ['id' => 'WF', 'name' => 'Wallis and Futuna', 'category' => self::tier(), 'utc_start' => 720],
            ['id' => 'YE', 'name' => 'Yemen', 'category' => self::tier(), 'utc_start' => 180],
            ['id' => 'ZM', 'name' => 'Zambia', 'category' => self::tier(), 'utc_start' => 120],
            ['id' => 'ZW', 'name' => 'Zimbabwe', 'category' => self::tier(), 'utc_start' => 120]
        ];
    }

    public function run() {
        DB::table('countries')->insert(self::getList());
    }
}
