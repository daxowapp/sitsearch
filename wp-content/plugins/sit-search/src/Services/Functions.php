<?php

namespace SIT\Search\Services;

class Functions
{

    public static $countries = [
        'Afghanistan' => 'af',
        'Albania' => 'al',
        'Algeria' => 'dz',
        'American Samoa' => 'as',
        'Andorra' => 'ad',
        'Angola' => 'ao',
        'Anguilla' => 'ai',
        'Antarctica' => 'aq',
        'Antigua and Barbuda' => 'ag',
        'Argentina' => 'ar',
        'Armenia' => 'am',
        'Aruba' => 'aw',
        'Australia' => 'au',
        'Austria' => 'at',
        'Azerbaijan' => 'az',
        'Bahamas' => 'bs',
        'Bahrain' => 'bh',
        'Bangladesh' => 'bd',
        'Barbados' => 'bb',
        'Belarus' => 'by',
        'Belgium' => 'be',
        'Belize' => 'bz',
        'Benin' => 'bj',
        'Bermuda' => 'bm',
        'Bhutan' => 'bt',
        'Bolivia' => 'bo',
        'Bosnia and Herzegovina' => 'ba',
        'Botswana' => 'bw',
        'Brazil' => 'br',
        'Brunei' => 'bn',
        'Bulgaria' => 'bg',
        'Burkina Faso' => 'bf',
        'Burundi' => 'bi',
        'Cambodia' => 'kh',
        'Cameroon' => 'cm',
        'Canada' => 'ca',
        'Cape Verde' => 'cv',
        'Central African Republic' => 'cf',
        'Chad' => 'td',
        'Chile' => 'cl',
        'China' => 'cn',
        'Colombia' => 'co',
        'Comoros' => 'km',
        'Congo' => 'cg',
        'Costa Rica' => 'cr',
        'Croatia' => 'hr',
        'Cuba' => 'cu',
        'Cyprus' => 'cy',
        'Czech Republic' => 'cz',
        'Democratic Republic of the Congo' => 'cd',
        'Denmark' => 'dk',
        'Djibouti' => 'dj',
        'Dominica' => 'dm',
        'Dominican Republic' => 'do',
        'Ecuador' => 'ec',
        'Egypt' => 'eg',
        'El Salvador' => 'sv',
        'Equatorial Guinea' => 'gq',
        'Eritrea' => 'er',
        'Estonia' => 'ee',
        'Eswatini' => 'sz',
        'Ethiopia' => 'et',
        'Fiji' => 'fj',
        'Finland' => 'fi',
        'France' => 'fr',
        'Gabon' => 'ga',
        'Gambia' => 'gm',
        'Georgia' => 'ge',
        'Germany' => 'de',
        'Ghana' => 'gh',
        'Greece' => 'gr',
        'Grenada' => 'gd',
        'Guatemala' => 'gt',
        'Guinea' => 'gn',
        'Guinea-Bissau' => 'gw',
        'Guyana' => 'gy',
        'Haiti' => 'ht',
        'Honduras' => 'hn',
        'Hungary' => 'hu',
        'Iceland' => 'is',
        'India' => 'in',
        'Indonesia' => 'id',
        'Iran' => 'ir',
        'Iraq' => 'iq',
        'Ireland' => 'ie',
        'Israel' => 'il',
        'Italy' => 'it',
        'Jamaica' => 'jm',
        'Japan' => 'jp',
        'Jordan' => 'jo',
        'Kazakhstan' => 'kz',
        'Kenya' => 'ke',
        'Kiribati' => 'ki',
        'Kuwait' => 'kw',
        'Kyrgyzstan' => 'kg',
        'Laos' => 'la',
        'Latvia' => 'lv',
        'Lebanon' => 'lb',
        'Lesotho' => 'ls',
        'Liberia' => 'lr',
        'Libya' => 'ly',
        'Liechtenstein' => 'li',
        'Lithuania' => 'lt',
        'Luxembourg' => 'lu',
        'Madagascar' => 'mg',
        'Malawi' => 'mw',
        'Malaysia' => 'my',
        'Maldives' => 'mv',
        'Mali' => 'ml',
        'Malta' => 'mt',
        'Mauritania' => 'mr',
        'Mauritius' => 'mu',
        'Mexico' => 'mx',
        'Moldova' => 'md',
        'Monaco' => 'mc',
        'Mongolia' => 'mn',
        'Montenegro' => 'me',
        'Morocco' => 'ma',
        'Mozambique' => 'mz',
        'Myanmar' => 'mm',
        'Namibia' => 'na',
        'Nauru' => 'nr',
        'Nepal' => 'np',
        'Netherlands' => 'nl',
        'New Zealand' => 'nz',
        'Nicaragua' => 'ni',
        'Niger' => 'ne',
        'Nigeria' => 'ng',
        'North Korea' => 'kp',
        'North Macedonia' => 'mk',
        'Northern Cyprus' => 'cy',
        'Norway' => 'no',
        'Oman' => 'om',
        'Pakistan' => 'pk',
        'Palau' => 'pw',
        'Palestine' => 'ps',
        'Panama' => 'pa',
        'Papua New Guinea' => 'pg',
        'Paraguay' => 'py',
        'Peru' => 'pe',
        'Philippines' => 'ph',
        'Poland' => 'pl',
        'Portugal' => 'pt',
        'Qatar' => 'qa',
        'Romania' => 'ro',
        'Russia' => 'ru',
        'Rwanda' => 'rw',
        'Saint Kitts and Nevis' => 'kn',
        'Saint Lucia' => 'lc',
        'Saint Vincent and the Grenadines' => 'vc',
        'Samoa' => 'ws',
        'San Marino' => 'sm',
        'Saudi Arabia' => 'sa',
        'Senegal' => 'sn',
        'Serbia' => 'rs',
        'Seychelles' => 'sc',
        'Sierra Leone' => 'sl',
        'Singapore' => 'sg',
        'Slovakia' => 'sk',
        'Slovenia' => 'si',
        'Solomon Islands' => 'sb',
        'Somalia' => 'so',
        'South Africa' => 'za',
        'South Korea' => 'kr',
        'South Sudan' => 'ss',
        'Spain' => 'es',
        'Sri Lanka' => 'lk',
        'Sudan' => 'sd',
        'Suriname' => 'sr',
        'Sweden' => 'se',
        'Switzerland' => 'ch',
        'Syria' => 'sy',
        'Taiwan' => 'tw',
        'Tajikistan' => 'tj',
        'Tanzania' => 'tz',
        'Thailand' => 'th',
        'Togo' => 'tg',
        'Tonga' => 'to',
        'Trinidad and Tobago' => 'tt',
        'Tunisia' => 'tn',
        'Turkey' => 'tr',
        'Turkmenistan' => 'tm',
        'Tuvalu' => 'tv',
        'Uganda' => 'ug',
        'Ukraine' => 'ua',
        'United Arab Emirates' => 'ae',
        'United Kingdom' => 'gb',
        'United States' => 'us',
        'Uruguay' => 'uy',
        'Uzbekistan' => 'uz',
        'Vanuatu' => 'vu',
        'Vatican City' => 'va',
        'Venezuela' => 've',
        'Vietnam' => 'vn',
        'Yemen' => 'ye',
        'Zambia' => 'zm',
        'Zimbabwe' => 'zw',
    ];

    public static function save_transient(string $key, $value, int $expiration = 0)
    {
        set_transient($key, $value, $expiration);
    }

    public static function get_transient(string $key)
    {
        return get_transient($key);
    }

    public static function create_field_if_needed($data, $group): int|\WP_Error
    {
        $post_parent = get_page_by_path($group, OBJECT, 'acf-field-group');
        $post_id = 0;
        $query = new \WP_Query([
            'post_type' => 'acf-field',
            'post_parent' => $post_parent->ID,
            'name' => $data['field_name']
        ]);

        $type = Functions::validate_acf_type($data['data_type']);
        $field = $data['field_name'];
        if (!$query->found_posts) {
            $field_data = array(
                'post_title' => $data['field_label'],
                'post_name' => $field,
                'post_excerpt' => $field,
                'post_status' => 'publish',
                'post_type' => 'acf-field',
                'post_author' => 1,
                'post_parent' => $post_parent->ID,
                'menu_order' => 0,
                'post_content' => serialize(
                    array(
                        'type' => $type,
                        'name' => $field,
                        'label' => $data['field_label'],
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => array(
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ),
                        'default_value' => '',
                        'placeholder' => '',
                        'prepend' => '',
                        'append' => '',
                        'maxlength' => '',
                    )
                ),
            );

            $post_id = wp_insert_post($field_data);
            if ($post_id) {
                update_field('field_' . $post_id, $data['field_name'], 'option');
            }
        }

        return $post_id;
    }

    private static function validate_acf_type(mixed $data_type)
    {
        switch ($data_type) {
            case 'profileimage':
                return 'file';

            case 'boolean':
                return 'true_false';

            case 'textarea':
                return 'textarea';

            case 'subform':
                return 'repeater';

            case 'lookup':
            case 'percent':
            case 'autonumber':
            case 'formula':
            case 'multiselectpicklist':
            case 'territories':
            case 'ownerlookup':
            case 'website':
            case 'integer':
            case 'bigint':
            case 'date':
            case 'email':
            case 'datetime':
            case 'picklist':
            case 'phone':
            default:
                return 'text'; // Default to 'text' if no specific match is found
        }
    }

    public static function getPostByMeta($meta_key, $meta_value, $post_type)
    {
        $args = array(
            'post_type' => $post_type,
            'meta_query' => array(
                array(
                    'key' => $meta_key,
                    'value' => $meta_value,
                    'compare' => '=',
                ),
            ),
        );

        $query = new \WP_Query($args);

        if ($query->have_posts()) {
            return $query->posts[0];
        }

        return null;
    }

    public static function getTermByMeta($meta_key, $meta_value, $taxonomy)
    {

        $args = array(
            'taxonomy' => $taxonomy,
            'hide_empty' => false,
            'meta_query' => array(
                array(
                    'key' => $meta_key,
                    'value' => $meta_value,
                    'compare' => '=',
                ),
            ),
        );

        $terms = get_terms($args);

        if (!empty($terms) && !is_wp_error($terms)) {
            return $terms[0];
        }

        return null;
    }

    public static function getCountryFlag($country_name)
    {
        $code = self::getCountryCode($country_name);
        if($country_name=='Northern Cyprus'){
            return \SIT\Search\Services\URLHelper::upload('2025/03/northern-cyprus.png');
        }
        return 'https://flagcdn.com/w640/' . $code . '.png';
    }

    public static function getCountryCode($country_name)
    {
        return self::$countries[$country_name];
    }
}