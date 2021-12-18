<?php

/**
 * @param null $user_id
 * @return bool
 */
function is_super_admin($user_id = null) {
    if ($user_id) {
        return User::find($user_id)->role == 'super';
    }
    return Auth::user()->role == 'super';
}

/**
 * @param int $user_id
 * @return bool
 */
function is_user_owner($user_id) {
    return Auth::user()->id == $user_id;
}

/**
 * @param $domain_id
 * @return bool
 */
function is_user_domain($domain_id) {
    $userDomains = Auth::user()->domains->lists('id');

    return in_array($domain_id, $userDomains);
}

/**
 * @param $domain_id
 * @return mixed
 */
function is_domain_private($domain_id) {
    return Domain::find($domain_id)->isPrivate();
}

/**
 * @param $domain_id
 * @return mixed
 */
function is_domain_default($domain_id) {
    return Domain::find($domain_id)->isDefault();
}

/**
 * @param $domain_id
 * @return bool
 */
function is_domain_taught($domain_id) {
    if (is_domain_private($domain_id)) {
        return is_user_domain($domain_id);
    }

    return true;
}

/**
 * @param $value,$key,$array
 * @return bool
 */
function searcharray($value, $key, $array) {
    foreach ($array as $k => $val) {
        if ($val[$key] == $value) {
            return $k;
        }
    }
    return null;
}

/**
 * 
 * @bigInt problem Bug fix
 * 
 */
function bigInt($obj) {
    if (version_compare(PHP_VERSION, '5.4.0', '>=') && !(defined('JSON_C_VERSION') && PHP_INT_SIZE > 4)) {
        /** In PHP >=5.4.0, json_decode() accepts an options parameter, that allows you
         * to specify that large ints (like Steam Transaction IDs) should be treated as
         * strings, rather than the PHP default behaviour of converting them to floats.
         */
        $obj = json_decode($obj, false, 512, JSON_BIGINT_AS_STRING);
    } else {
        /** Not all servers will support that, however, so for older versions we must
         * manually detect large ints in the JSON string and quote them (thus converting
         * them to strings) before decoding, hence the preg_replace() call.
         */
        $max_int_length = strlen((string) PHP_INT_MAX) - 1;
        $json_without_bigints = preg_replace('/:\s*(-?\d{' . $max_int_length . ',})/', ': "$1"', $obj);
        $obj = json_decode($json_without_bigints);
    }
    return $obj;
}

/**
 * @param int $domain_id
 * @param bool $object
 * @return string
 */
function calculate_learning_percent($domain_id = 0, $object = false) {
    $itemsLearned = DB::table('sentimental')->where('domain_id', $domain_id)->count();
    $limitsLearned = Config::get('settings.learning_limit');

    $domain = Domain::find($domain_id);

    $percentageLearning = ($itemsLearned) ? ($itemsLearned / $limitsLearned) * 100 : $itemsLearned;
    $percentageLearning = ($percentageLearning > 100) ? 100 : $percentageLearning;

    if ($object) {
        $result = array(
            'itemsLearned' => $itemsLearned,
            'limitsLearned' => $limitsLearned,
            'percentageLearning' => $percentageLearning,
            'domainNames' => $domain->getNames()
        );

        return $object = json_decode(json_encode($result), false);
    }

    return "% $percentageLearning / $itemsLearned";
}

/**
 * Avoiding dublicate names under same ORG.
 * 
 * @param  int  $id
 * @return Response
 */
function uniqueName($obj, $param) {

    //It has bug - look 
    //http://stackoverflow.com/questions/35986243/error-when-using-regexp-in-mysql
    //$slugCount = count($obj::whereRaw("name REGEXP '^{$param}(-[0-9]*)?$'")->get());
    $slugCount = count($obj::whereRaw("name = '{$param}'")->get());
    return ($slugCount > 0) ? $param . '-' . $slugCount : $param;
}

/**
 * @param $str
 * @return string
 */
function tr_strtolower($str) {
    $lower = array('ç', 'ğ', 'i', 'ı', 'ö', 'ş', 'ü');
    $upper = array('Ç', 'Ğ', 'İ', 'I', 'Ö', 'Ş', 'Ü');
    return strtolower(str_replace($upper, $lower, $str));
}

/**
 * levenstein Fix 
 * 
 */
function utf8_to_extended_ascii($str, &$map) {
    // find all multibyte characters (cf. utf-8 encoding specs)
    $matches = array();
    if (!preg_match_all('/[\xC0-\xF7][\x80-\xBF]+/', $str, $matches))
        return $str; // plain ascii string




        
// update the encoding map with the characters not already met
    foreach ($matches[0] as $mbc)
        if (!isset($map[$mbc]))
            $map[$mbc] = chr(128 + count($map));

    // finally remap non-ascii characters
    return strtr($str, $map);
}

function levenshtein_utf8($s1, $s2) {
    $charMap = array();
    $s1 = utf8_to_extended_ascii($s1, $charMap);
    $s2 = utf8_to_extended_ascii($s2, $charMap);

    return levenshtein($s1, $s2);
}

/*
 * End
 */

/**
 * @param bool $session
 */
function update_tokens($session = true) {
    // if session true and session has tokens get tokens from session else from db new token
    if ($session && Session::has('tokens')) {
        $tokens = Session::get('tokens');
    } else {
        // get active tokens from apis where not used in 15 minutes.
        $tokens = Apis::Active()->select()->
                        where('updated_at', '<=', DB::raw('DATE_ADD(now(), INTERVAL -15 MINUTE)'))->first();
        // update timestamp updated_at
        $tokens->touch();

        // tokens to store to session
        Session::set('tokens', $tokens);
    }

//    dd($tokens);
    // set new tokens config file.
    Config::set('thujohn/twitter::CONSUMER_SECRET', $tokens->api_secret);
    Config::set('thujohn/twitter::CONSUMER_KEY', $tokens->api_key);
    Config::set('thujohn/twitter::ACCESS_TOKEN', $tokens->access_token);
    Config::set('thujohn/twitter::ACCESS_TOKEN_SECRET', $tokens->access_secret);
}
