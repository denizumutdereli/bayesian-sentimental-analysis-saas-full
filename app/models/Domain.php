<?php

use Illuminate\Database\Eloquent\SoftDeletingTrait;

class Domain extends \Eloquent {

    use SoftDeletingTrait;

    protected $softDelete = true;
    protected $table = 'domains';
    protected $fillable = [
        'user_id',
        'account_id',
        'name',
        'settings',
        'sense',
        'domain_secret',
        'is_active',
        'is_default',
        'is_private',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
        'deleted_at'
    ];
    protected static $settings = null;

    /**
     * A many-to-one relationship domain to account
     *
     * @return mix
     */
    public function account() {
        return $this->belongsTo('Account');
    }

    /**
     * A many-to-one relationship domain to user
     *
     * @return mix
     */
    public function user() {
        return $this->belongsTo('User');
    }

    /**
     * A many-to-one relationship sentimentals to domain
     *
     * @return mix
     */
    public function sentimentals() {
        return $this->hasMany('Sentimental');
    }

    public function isDefault() {
        return $this->getAttribute('is_default');
    }

    public function isPrivate() {
        return $this->getAttribute('is_private');
    }

    public function scopeGetDefault($query) {
        return $query->where('is_default', 1)->first();
    }

    public function getSettings() {
        if ($this->settings && self::$settings == null) {
            self::$settings = json_decode($this->settings, true);
        }

        return self::$settings;
    }

    public function getSources() {
        if ($this->settings) {
            return $this->getSettings()["sources"];
        }
        return array();
    }

    public function getTags() {
        if ($this->settings) {
            return $this->getSettings()["tags"];
        }
        return array();
    }

    function getAdjustments() {
        if ($this->settings) {
            return $this->getSettings()["adjustment"];
        }
        return array();
    }

    function getNames() {
        if ($this->settings) {
            return $this->getSettings()["names"];
        }
        return array();
    }

    function getModel() {
        if ($this->settings) {
            return $this->getSettings()["model"];
        }
        return array();
    }
    
    function getRules() {
        $rules = array();
        if ($this->settings) {
            $settings = $this->getSettings();
            $ruleNames = array("unigram" => 'Unigram', "bigram" => 'Bigram', '3gram' => 'Trigram', '4gram' => 'Fourgram');
            foreach ($settings["rules"] as $_rules) {
                $rule = array();
                foreach ($_rules as $value) {
                    $rule[$value] = $ruleNames[$value];
                }
                $rules[] = $rule;
            }
        }
        return $rules;
    }

    function useBalance() {
        if ($this->settings) {
            return $this->getSettings()["balance"] == "1";
        }
        return false;
    }

}
