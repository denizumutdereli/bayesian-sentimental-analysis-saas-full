<?php

namespace Ynk\Bayes;

use Classifier;
use Config;
use DB;
use Sentimental;

/**
 * Class Bayes
 * @package Ynk\Bayes
 */
class Bayes {

    /**
     * @var array
     */
    static $words = array();

    /**
     * @var array
     */
    static $badwords = array();

    /**
     * @var array
     */
    static $hashtags = array();

    /**
     * @var array
     */
    static $mentions = array();

    /**
     * @var array
     */
    static $sentences = array();

    /**
     * @var array
     */
    static $score;

    /**
     * @var null
     */
    protected static $domain = null;

    /**
     * @param        $text
     * @param string $sourceType
     * @param null $domainId
     * @param int $precision
     * @internal param string $type
     *
     * @return mixed
     */
    protected $user;

    /**
     *
     */
    public function __construct() {
        $this->user = \Auth::user();
    }

    public function check($text, $sourceType = 'upload', $domainId = null, $precision = 0) {

        $text = Bayes::removeDublicateChars($text);
        Bayes::$badwords = Bayes::findTags($text, $domainId);
        Bayes::$hashtags = Bayes::getHashtags($text);
        Bayes::$mentions = Bayes::getMentions($text);
        Bayes::$sentences = Bayes::sentence_boundaries($text);
        $text = $this->cleanWord($text);

        $results = ['negative' => 0, 'positive' => 0, 'neutral' => 0];

        if ($domainId == null) {
            $results['neutral'] = 100;
            return $results;
        }

        if (self::$domain == null) {
            self::$domain = \Domain::find($domainId);
        }

        $rules = self::$domain->getRules();
        $model = self::$domain->getModel();

        $results['model'] = Config::get('settings.bayes.models')[$model];

        $keys = [-1 => 'negative', 1 => 'positive', 0 => 'neutral'];

        $totalRuleCount = 0;
        foreach ($rules as $number => $ruleCollection) {

            foreach ($ruleCollection as $rule => $name) {

                $totalRuleCount++;

                $results[$number][$rule] = ['negative' => 0, 'positive' => 0, 'neutral' => 0];

                $times = $this->getTimes($rule);

                foreach (Bayes::$sentences as $sentence) {

                    if ($model == 0) {
                        $temp = $this->nGram($sentence, $times, $sourceType, self::$domain->id, $model);
                    } else {
                        $temp = $this->nGramWord($sentence, $times, $sourceType, self::$domain->id, $model);
                    }

                    foreach ($temp as $key => $value) {
                        $results[$key] += $value;
                        $results[$number][$rule][$key] += $value;

                        if ($model == 0) {
                            if (!isset($results[$number][$rule]['score'])) {
                                $results[$number][$rule]['score'] = 0;
                            }
                            $results[$number][$rule]['score'] += Bayes::$score;
                        }
                    }
                }

                foreach ($keys as $key) {
                    $results[$number][$rule][$key] = ($results[$number][$rule][$key] / count(Bayes::$sentences, 1));
                    if ($model == 0) {
                        $results[$number][$rule]['score'] = ($results[$number][$rule]['score'] / count(Bayes::$sentences, 1));
                    }
                }
            }
        }

        foreach (Bayes::$sentences as $sentence) {
            $results['sentences'][] = $sentence;
        }

        foreach ($keys as $key) {
//            if (self::$domain->useBalance()) {
            $results[$key] = number_format($results[$key] / ($totalRuleCount * count(Bayes::$sentences)), 2, '.', '');
            ;
//            } else {
            //$results[$key] = round(($results[$key] / ($totalRuleCount * count($sentences))), $precision);
            //}
        }

        // if domain has balance
        if (self::$domain->useBalance()) {
            $total = 0;
            $adjustments = self::$domain->getAdjustments();

            foreach ($adjustments as $k => $value) {
                $total += $value;
            }

            if ($total == 100) {
                foreach ($keys as $key => $name) {
                    $results[$name] = round((($results[$name] + $adjustments[$key]) / 2), $precision);
                }
            }
        }

        $subtotal = 0;
        foreach ($keys as $key => $name) {
            $subtotal += $results[$name];
        }

        //splitting
        $results['badwords'] = Bayes::$badwords;
        $results['hashtags'] = Bayes::$hashtags;
        $results['mentions'] = Bayes::$mentions;

        return $results;
    }

    /**
     * find badwords from whole text and returns as array
     *
     * @param string|array $text
     *
     * @return array badwords list
     *
     */
    public static function findTags($text, $domainId, $tags_query = null) {

        if (!is_array($text)) {
            $text = explode(' ', $text);
        }
        $text_words = null;
        foreach ($text as $word) {
            // remove dublicate chars
            $word = tr_strtolower(Bayes::removeNonLetterChars($word), 'UTF-8');

            if (false === mb_check_encoding($word, 'UTF-8')) {
                $word = mb_convert_encoding($word, 'UTF-8');
            }
            $text_words[] = $word;
        }

        $domain = \Domain::find($domainId);

        if ($tags_query == null)
            $tags = $domain->getTags();
        else
            $tags = $tags_query;

        Bayes::$words = array();

        // get all words if words not populated
        if ($tags) {

            $temp = array();

            $words = DB::table('tag_uploads')->select('*')->whereIn('tag_id', $tags)->whereIn('tag', $text_words)->get();



            foreach ($words as $word) {

                $tag_cat = \Tag::find($word->tag_id);

                Bayes::$words[$tag_cat->id][$word->id] = tr_strtolower(mb_convert_encoding($word->tag, 'UTF-8'));
            }

            // we cannot find word in white or blacklist as exact match so
            // we must use levenshtein algo.
            if (Config::get('settings.levenshtein.suspects', false) OR $domain->sense == 1) {

                $l_words = DB::table('tag_uploads')->select('*')->whereIn('tag_id', $tags)->get();

                foreach ($text_words as $w) {

                    //PHP Bug whitespaces after explode ''
                    $w = tr_strtolower($w, 'UTF-8');

                    if (strlen($w) >= 5 and strlen($w) <= 20) {
                        foreach ($l_words as $suspect) {

//                            $stag = strlen($suspect->tag);
//                            $sw = strlen($w);
//                            $conc = $stag-$sw;
//                            if($conc < 0) $conc = $conc * -1;


                            $score = levenshtein_utf8($suspect->tag, trim($w));

                            if ($score <= Config::get('settings.levenshtein.score', 2)) {

                                $tag_cat = \Tag::find($suspect->tag_id);

                                if (!in_array($w, Bayes::$words) && $w != '') {
                                    Bayes::$words[$tag_cat->id][$suspect->id] = tr_strtolower(mb_convert_encoding(trim($w), 'UTF-8'));
                                }
                            }
                        }
                    }
                }
            }
        }
        $result = Bayes::$words;

////        echo '<pre>';
//        print_r($result);
//        exit;

        return $result;
    }

    /**
     * gets hashtags
     *
     * @param $text
     *
     * @return mixed
     */
    public static function getHashtags($text) {
        preg_match_all('/#([\p{Pc}\p{N}\p{L}\p{Mn}]+)/u', $text, $matches);
        return $matches[0];
    }

    /**
     * gets mentions
     *
     * @param $text
     *
     * @return mixed
     */
    public static function getMentions($text) {
        preg_match_all('/(@\w+)/', $text, $matches);
        return $matches[0];
    }

    /**
     * remove dublicate chars if repeated more then two
     *
     * @param $text
     *
     * @return mixed
     */
    public static function removeDublicateChars($text) {
        $text = preg_replace('/\s+/', ' ', $text); //remove whitespaces more then one.
        $text = preg_replace('/([#İıöşçüğÖÇŞĞÜa-zA-Z0-9.\s])\1{2,}/imu', '$1$1', $text);
        return $text;
    }

    /**
     * @param $text
     * @return mixed
     */
    public static function removeNonLetterChars($text) {
        $text = strip_tags($text);
        $text = preg_replace('/([^İıöşçüğÖÇŞĞÜa-zA-Z0-9]+)/iu', ' ', $text);
        return $text;
    }

    /**
     * @param $text
     * return array
     */
    public static function sentence_boundaries($text) {
        $re = '/# Split sentences on whitespace between them.
(?<=                # Begin positive lookbehind.
  [.!?]             # Either an end of sentence punct,
| [.!?][\'"]        # or end of sentence punct and quote.
)                   # End positive lookbehind.
(?<!                # Begin negative lookbehind.
  Mr\.              # Skip either "Mr."
| Mrs\.             # or "Mrs.",
| Ms\.              # or "Ms.",
| Jr\.              # or "Jr.",
| Dr\.              # or "Dr.",
| Prof\.            # or "Prof.",
| Sr\.              # or "Sr.",
| \s[A-Z]\.              # or initials ex: "George W. Bush",
                    # or... (you get the idea).
)                   # End negative lookbehind.
\s+                 # Split on whitespace between sentences.
/ix';
        $sentences = preg_split($re, $text, -1, PREG_SPLIT_NO_EMPTY);
        return $sentences;
    }

    /**
     * @param $return
     * @return mixed
     */
    public function setDefaultResult($return) {
        if (!isset($return['positive'])) {
            $return['positive'] = 0;
        }
        if (!isset($return['negative'])) {
            $return['negative'] = 0;
        }
        if (!isset($return['neutral'])) {
            $return['neutral'] = 0;
        }
        return $return;
    }

    /**
     * @param     $text
     * @param int $times
     * @param string $sourceType
     * @param null $domain
     * @return array
     */
    protected function Bigrams($word) {
        $ngrams = array();
        $len = strlen($word);
        for ($i = 0; $i + 1 < $len; $i++) {
            $ngrams[$i] = $word[$i] . $word[$i + 1];
        }
        return $ngrams;
    }

    /**
     * @param     $text
     * @param int $times
     * @param string $sourceType
     * @param null $domain
     * @return array
     */
    protected function Trigrams($word) {
        $ngrams = array();
        $len = strlen($word);
        for ($i = 0; $i + 2 < $len; $i++) {
            $ngrams[$i] = $word[$i] . $word[$i + 1] . $word[$i + 2];
        }
        return $ngrams;
    }

    /**
     * @param     $text
     * @param int $times
     * @param string $sourceType
     * @param null $domain
     * @return array
     */
    protected function Ngramer($word, $n = 3) {
        $len = strlen($word);
        $ngram = array();

        for ($i = 0; $i + $n <= $len; $i++) {
            $string = "";
            for ($j = 0; $j < $n; $j++) {
                $string.=$word[$j + $i];
            }
            $ngram[$i] = $string;
        }

        return $ngram;
    }

    /**
     * @param     $text
     * @param int $times
     * @param string $sourceType
     * @param null $domain
     * @return array
     */
    protected function nGram($text, $times = 2, $sourceType = "twitter", $domain = null, $model = 0) {

        $words = preg_split('/ /', $text);
        $result = array();

        foreach ($words as $val) {
            switch ($times) {
                case '2':
                    $ngram = $this->Bigrams($val);
                    break;

                case '3':
                    $ngram = $this->Trigrams($val);
                    break;

                default:
                    $ngram = $this->Ngramer($val, $times);
                    break;
            }

            if (sizeof($ngram) > 0) {
                $result[] = $ngram;
            }
        }

        $data = array();

        foreach ($result as $key) {

            for ($i = 0; $i <= sizeof($key) - 1; $i++) {
                $data[] = $key[$i] . '*';
            }
        }

        $words = implode(',', $data) . '';

        $result = $this->query($words, $sourceType, $domain, $model);

        return $result;
    }

    /**
     * @param     $text
     * @param int $times
     * @param string $sourceType
     * @param null $domain
     * @return array
     */
    protected function nGramWord($text, $times = 2, $sourceType = "twitter", $domain = null, $model = 0) {
        $words = preg_split('/ /', $text);
        $result = array();
        $temp = '';

        $words = array_values(array_filter($words, function($s) {
                    return !preg_match("/\{\w\}/", $s);
                }));

        $len = count($words);
        for ($i = 0; $i <= $len - $times; $i++) {
            for ($d = 0; $d < $times; $d++) {

                //clear asterix
                $words[$i + $d] = str_replace('*', '', $words[$i + $d]);

                //master word // Beta TR
                if (strlen($words[$i + $d]) > 7)
                    $words[$i + $d] = substr($words[$i + $d], 0, -2);

                if (strlen($words[$i + $d]) > 2) {
                    $words[$i + $d] = $words[$i + $d] . '*'; //conjuction
                }
                $temp .= ' ' . $words[$i + $d];
            }

            $result[] = trim($temp);
            $temp = '';
        }

        $words = implode(',', $result) . '';

        $result = $this->query($words, $sourceType, $domain, $model);

        return $result;
    }

    /**
     * @param $result
     *
     * @return mixed
     */
    private function calculate($result) {

        $score = 0;
        $i = 0;
        foreach ($result as $mention) {
            $i++;
            $score = $score + $mention->state;
        }

        if ($i == 0)
            $i++;

        self::$score = number_format($score / $i, 2, ".", ",");

        /**
         * Score Table
         * 0.5 to 1 Very Positive
         * 0.1 to 0.5 Positive
         * -0.1 to 0.1 Neutral
         * -0.1 to -0.5 Negative
         * -0.5 to -1 and higher Very Negative
         */
        switch (Bayes::$score) {
            case (Bayes::$score >= 0.5):
                $return['positive'] = 100;
                break;
            case (Bayes::$score >= 0.1):
                $return['positive'] = 100;
                //$return['neutral'] = 10;
                break;
            case (Bayes::$score >= -0.1):
                $return['neutral'] = 100;
                break;
            case (Bayes::$score >= -0.5):
                $return['negative'] = 100;
                break;
            case (Bayes::$score < -0.1):
                $return['negative'] = 100;
                //$return['neutral'] = 10;
                break;

            default:
                $return['positive'] = 0;
                $return['negative'] = 0;
                $return['neutral'] = 100;
                break;
        }

        return $return;
    }

    /**
     * @param $result
     *
     * @return mixed
     */
    private function calculateWord($result) {
        $data = [];
        $total = 0;
        foreach ($result as $row) {
            $total++;
            @$data[$row->state] ++;
        }

        if (empty($data)) {
            return ['positive' => 0, 'negative' => 0, 'neutral' => 100];
        }

        $keys = [-1 => 'negative', 1 => 'positive', 0 => 'neutral'];

        foreach ($data as $key => $val) {
            if (isset($data[$key])) {
                $return[$keys[$key]] = (($data[$key] / $total) * 100);
            }
        }

        $return = $this->setDefaultResult($return);

        return $return;
    }

    /**
     * @param $words
     *
     * @param string $sourceType
     * @param null $domain
     * @return array
     */
    protected function query($words, $sourceType = "twitter", $domain = null, $model = 0) {
        $limit = Config::get('settings.sentimental_limit');
        $params = ['words' => trim($words)];
        $where = "";

        if ($domain !== null) {
            $where .= sprintf(" AND domain_id = :domain", $domain);
            $params["domain"] = $domain;
        }
        /*
          if($sourceType != null) {
          $where .= " AND source = :source";
          $params["source"] = $sourceType;
          } */

        if ($limit > 0) {
            $where .= " LIMIT 0, $limit";
        }

        try {

            $result = DB::select(DB::raw('SELECT * FROM sentimental WHERE MATCH ( text ) AGAINST (:words IN BOOLEAN MODE)' . $where), $params);
        } catch (\Illuminate\Database\QueryException $e) {
            return $this->calculate(array());
        }

        if ($model == 0)
            return $this->calculate($result);
        else
            return $this->calculateWord($result);
    }

    /**
     * @param     $text
     * @param int $state
     * @param string $source source adı - twitter, yorum, vb.
     * @param int $sourceId source id, twitter için twitter id, yorumlar için yorum id vb.
     *
     * @return \Illuminate\Database\Eloquent\Model|static
     */
    public function learn($text, $state = 0, $source = '', $sourceId = null, $domainId) {
        $text = $this->cleanWord($text);

        if (!$this->user->is_superAdmin())
            $accountId = $this->user->account_id;
        else {
            $domain = \Domain::find($domainId);
            $accountId = $domain->account_id;
        }

        if (strlen($text) > 0) {
            $data = [
                'text' => $text,
                'state' => $state,
                'domain_id' => $domainId,
                'user_id' => $this->user->id,
                'account_id' => $accountId,
                'created_by' => $this->user->id,
                'updated_by' => $this->user->id
            ];

            if ($source) {
                $data['source'] = $source;
            }
            if ($sourceId) {
                $data['source_id'] = $sourceId;
            }

            if ($domainId) { // sourceId could not take as refference because it could be deleted
                $result = Sentimental::whereRaw('source_id = ? and domain_id = ? and text = ?', array(
                            $sourceId,
                            $domainId,
                            $text
                        ))->first();

                if ($result) {
                    return array('result' => $result, 'status' => 'old');
                }
            }

            $result = Sentimental::create($data);


            //Record the request
            \Ping::create(
                    [
                        'account_id' => $this->user->account_id,
                        'user_id' => $this->user->id,
                        'section' => 'sentimental',
                        'amount' => 1
                    ]
            );

            return array(
                'result' => $result,
                'status' => 'new'
            );
        }

        return null;
    }

    /**
     * @param $type
     *
     * @return mixed
     */
    public function getTimes($type) {

        switch ($type) {
            case 'monogram':
            case 'unigram':
                return 1;
                break;
            case 'bigram':
                return 2;
                break;
            case '3gram':
                return 3;
                break;
            case '4gram':
                break;
            default:
                return 1;
        }

        if ($type == 'monoGram' || strtolower($type) == 'unigram') {
            return 1;
        }

        if ($type == 'bGram' || strtolower($type) == 'bigram') {
            return 2;
        }

        $times = substr($type, 0, 1);

        return $times;
    }

    /**
     * @param $type
     *
     * @return mixed
     */
    public function getType($type) {
        return in_array($type, ['unigram', 'bigram', '3gram', '4gram']) ? $type : 'unigram';
    }

    /**
     * @param $text
     *
     * @return mixed
     */
    public static function cleanWord($text) {

        // clean spaces
        $text = trim($text);

        // remove web urls.
        $text = preg_replace('@(https?: //([-\w\.]+)+(/([\w/_\.]*(\?\S+)?(#\S+)?)?)?)@', '', $text);

        //mentions
        $text = preg_replace('/@(\w+)/iu', '', $text);

        //hahstags
        $text = preg_replace('/\s+#(\w+)/u', '|', $text);

        //remove sentence operators
//        $text = preg_replace('/ ama /iu', ' ', $text);
//        $text = preg_replace('/ amma /iu', ' ', $text);
//        $text = preg_replace('/ amma velakin /iu', ' ', $text);
//        $text = preg_replace('/ ancak /iu', ' ', $text);
//        $text = preg_replace('/ belki /iu', ' ', $text);
//        $text = preg_replace('/ bile /iu', ' ', $text);
//        $text = preg_replace('/ bre /iu', ' ', $text);
//        $text = preg_replace('/ da /iu', ' ', $text);
//        $text = preg_replace('/ de /iu', ' ', $text);
//        $text = preg_replace('/ dahi /iu', ' ', $text);
//        $text = preg_replace('/ eğer /iu', ' ', $text);
//        $text = preg_replace('/ fakat /iu', ' ', $text);
//        $text = preg_replace('/ gelgelelim /iu', ' ', $text);
//        $text = preg_replace('/ gerek /iu', ' ', $text);
//        $text = preg_replace('/ ha /iu', ' ', $text);
//        $text = preg_replace('/ halbuki /iu', ' ', $text);
//        $text = preg_replace('/ hatta /iu', ' ', $text);
//        $text = preg_replace('/ hele /iu', ' ', $text);
//        $text = preg_replace('/ hem /iu', ' ', $text);
//        $text = preg_replace('/ ile /iu', ' ', $text);
//        $text = preg_replace('/ ille velakin /iu', ' ', $text);
//        $text = preg_replace('/ ister /iu', ' ', $text);
//        $text = preg_replace('/ ki /iu', ' ', $text);
//        $text = preg_replace('/ kim /iu', ' ', $text);
//        $text = preg_replace('/ lakin /iu', ' ', $text);
//        $text = preg_replace('/ madem /iu', ' ', $text);
//        $text = preg_replace('/ mademki /iu', ' ', $text);
//        $text = preg_replace('/ meğer /iu', ' ', $text);
//        $text = preg_replace('/ meğerse /iu', ' ', $text);
//        $text = preg_replace('/ neyse /iu', ' ', $text);
//        $text = preg_replace('/ oysa /iu', ' ', $text);
//        $text = preg_replace('/ oysaki /iu', ' ', $text);
//        $text = preg_replace('/ şayet /iu', ' ', $text);
//        $text = preg_replace('/ ve /iu', ' ', $text);
//        $text = preg_replace('/ velev /iu', ' ', $text);
//        $text = preg_replace('/ veya /iu', ' ', $text);
//        $text = preg_replace('/ veyahut /iu', ' ', $text);
//        $text = preg_replace('/ ya /iu', ' ', $text);
//        $text = preg_replace('/ ya da /iu', ' ', $text);
//        $text = preg_replace('/ yahut /iu', ' ', $text);
//        $text = preg_replace('/ yani /iu', ' ', $text);
//        $text = preg_replace('/ yok /iu', ' ', $text);
//        $text = preg_replace('/ yoksa /iu', ' ', $text);
//        $text = preg_replace('/ zira /iu', ' ', $text);
//        $text = preg_replace('/Ya /iu', ' ', $text);
        //->apostroph
        $text = preg_replace('/,/iu', ' ', $text);
        $text = str_replace(':D', '', $text);
        $text = str_replace(')', '', $text);
        $text = str_replace('(', '', $text);
        $text = str_replace(';', '', $text);
        $text = str_replace(':', '', $text);
        $text = str_replace('!', '', $text);
        $text = str_replace('"', '', $text);
        $text = str_replace('?', '', $text);
        $text = str_replace('.', '', $text);

        $text = trim($text);
        $text = strip_tags($text);

        return $text;
    }

    /**
     * Change state of a learned item
     *
     * @param $id
     * @param $state
     *
     * @return bool
     */
    public function changeState($id, $state = -1) {
        $sentence = Sentimental::find($id);
//        echo $id;
//        exit;
        if ($sentence) {
            $sentence->state = $state;

            return $sentence->save();
        }

        return false;
    }

    ##################
    /* classifier */
    ##################

    /**
     * @param $text
     * @param $tag_id
     *
     * @param string $delimeter
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function learnClassifier($text, $tag_id, $delimeter = ' ') {
        $text = $this->cleanWord($text);
        $words = explode($delimeter, $text);

        if (count($words) > 1) {
            foreach ($words as $word) {
                $items[] = array(
                    'word' => $word,
                    'tag_id' => $tag_id
                );
            }
        } else {
            $items = array('word' => $words, 'tag_id' => $tag_id);
        }

        return (bool) Classifier::insert($items);
    }

    /**
     * change words tag
     *
     * @param $id
     * @param $tag_id
     * @return bool
     */
    public function changeClassifier($id, $tag_id) {
        $word = Classifier::find($id);
        $word->tag_id = $tag_id;

        return (bool) $word->save();
    }

    /**
     * to get tags by text
     *
     * @param        $text
     * @param string $delimeter
     *
     * @return mixed
     */
    public function checkClassifier($text, $delimeter = ' ') {
        $words = explode($delimeter, $text);

        return Classifier::join('tags', 'tags.id', '=', 'classifiers.tag_id')->whereIn('classifiers.word', $words)
                        ->get(['tags.id', 'tags.name']);
    }

}
