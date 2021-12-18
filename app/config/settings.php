<?php
#here you can change all settings. (Including api throting limits and user roles and other settings.)
#most of them are in Turkish but please I believe you can easly get the meaning by the array key names.
#dont forget to replace your API keys from providers.
return array(
    'domain' => '',
    'auth' => array(
        'soft_delete' => false,
    ),
    'api' => [
        'url' => 'http://api.labelai.com/',
        'version' => '1.0',
        'rates' => [
            'auth' => [ 'limit' => 100000, 'period' => 525600, 'cost' => 1, 'price' => 0.01],
            'check' => [ 'limit' => 100, 'period' => 10, 'cost' => 1, 'price' => 0.01],
            'images' => [ 'limit' => 10, 'period' => 10, 'cost' => 1, 'price' => 0.1],
            'context' => ['limit' => 100, 'period' => 10, 'cost' => 1, 'price' => 0.01],
            'postag' => [ 'limit' => 10, 'period' => 10, 'cost' => 1, 'price' => 0.1],
            'default' => [ 'limit' => 100, 'period' => 10, 'cost' => 1, 'price' => 0.01],
        ],
        'badrequest' => [
          'maxrequest' => 10,
          'timetowait' => 10, 
        ],
        'yandex' => [
            'api_key' => 'Yandex Translation API Key'
        ],
        'google' => [
            'bucket' => 'sentima',
            'api_key' => 'Cloud Key'
        ],
        'clarifia' => [
            'api_url' => 'https://api.clarifai.com/v1/',
            'client_id' => 'Your ID',
            'client_secret' => 'Your Secret',
        ],
        'aws' => [
            'sqs' => [
                'key' => '******', // AWS Access Key ID
                'secret' => '*******', //  AWS Secret Access Key
                'QueueUrl' => 'https://******/RulesQueue',
                'region' => 'eu-west-1',
            ],
            's3' => [
                'key' => '*********', // AWS Access Key ID
                'secret' => '*****', //  AWS Secret Access Key
                'bucket' => 'bucket name',
                'region' => 'eu-west-1',
            ],
        ],
        'functions' => [
            'image' => [
                'max_result' => 5,
                'max_size' => 1000000,
            ],
        ]
    ],
    'sentimental_limit' => 0,
    'timeline' => array(
        'comments' => true,
        'tweets' => false,
    ),
    'account' => array(
        'type' => array(
            'pitching' => 'Pitching',
            'live' => 'Live'
        ),
        'user_count' => 10, //Max. user cour per account
        'package' => [
            'limit' => [
                '50' => '50K - pitching - Free/14days', //pitch
                '250' => '250K - $500/m',
                '500' => '500K - $1,000/m',
                '-1' => '1M - unlimited - $1,500/m', //unlimited
            ],
        ],
        'trashed' => [
            'delete_time' => 14,
        ],
        'pitching' => [
            'period' => 14,
        ],
        'price' => [
            'request' => '0.01',
            'image' => '0.1'
        ],),
    'user' => array(
        'roles' => array(
            'super' => 'Super Admin',
            'admin' => 'Admin',
            'moderator' => 'Moderator',
        ),
    ),
    'analysis' => array(
        'limit' => 1000,
        'publishable' => array(51, 100), // bayes result range 60-100
        'unpublishable' => array(51, 100), // bayes result range 60-100
        'cache_time' => 60, // 0 is no cache
        'cache_forever' => false, // minutes
    ),
    'loop' => array(
        'tweet' => 100,
        'comment' => 100
    ),
    'loading' => array(
        'message' => 'Analiz işlemleri beklenenden uzun sürebilir. Lütfen bekleyiniz...',
    ),
    'sources' => array(
        'types' => array(
            'manual' => 'Manuel',
            'twitter' => 'Twitter',
            'comment' => 'Yorum'),
        'limit' => 5
    ),
    'uploads' => array(
        'limit' => ['file' => 5, 'line' => 5000]
    ),
    'tags' => array(
        'maxlength' => 50,
        'limit' => 5
    ),
    'taguploads' => array(
        'limit' => 5000
    ),
    'bwatch' => [
        'limit' => 10, //number of accounts
        'rules' => [
            'limit' => 10 //number of rules
        ]
    ],
    'states' => array(
        '1' => 'Olumlu',
        '-1' => 'Olumsuz',
        '0' => 'Nötr'
    ),
    'learning_limit' => 5000,
    'levenshtein' => array(
        'suspects' => false,
        'score' => 2
    ),
    'bayes' => array(
        'split_text' => true,
        'models' => [
            '0' => 'Acoustic TR',
            '1' => 'Naive Bayes',
        ],
    ),
    'comments' => array(
        'publish_url' => 'http://google.com/comments/bos',
        'delete_url' => 'http://google.com/comments/bos',
        'cache_time' => 0, // 0 is no cache
    ),
    'ticket' => array(
        'statuses' => array(
            0 => 'Kapalı',
            1 => 'Açık',
        )
    ),
    'actions' => array(
        'defaults' => array(
            'AuthController.getLogout',
            'AuthController.getChangepassword',
            'AuthController.postChangepassword',
            'HomeController.index',
            'UserController.index',
            'UserController.edit',
        ),
        'limited' => array(
            'account' => array(
                'active' => true,
                'label' => 'Hesap izinleri',
                'actions' => array(
                    'AccountController.create' => 'Hesap oluşturma',
                    'AccountController.index' => 'Hesap listesi',
                    'AccountController.edit' => 'Hesap düzenleme',
                    'AccountController.destroy' => 'Hesap Silme',
                    'AccountController.auth' => 'API Anahtarı Oluşturma',
                    'AccountController.revoke' => 'API Anahtarı Silme',
                    'AccountController.change' => 'API Anahtarı Değiştirme'
                )
            ),
            'user' => array(
                'active' => true,
                'label' => 'Kullanıcı izinleri',
                'actions' => array(
                    'UserController.create' => 'Kullanıcı oluşturma',
                    'UserController.index' => 'Kullanıcı listesi',
                    //'UserController.teamAdd' => 'Takım Oluşturma/Düzenleme',
                    'UserController.edit' => 'Kullanıcı düzenleme',
                    'UserController.destroy' => 'Kullanıcı Silme',
                )
            ),
            'invoice' => array(
                'active' => true,
                'label' => 'Fatura izinleri',
                'actions' => array(
                    'InvoiceController.index' => 'Fatura listesi',
                    'InvoiceController.show' => 'Fatura görüntüleme'
                )
            ),
            'bwatch' => array(
                'active' => true,
                'label' => 'BW izinleri',
                'actions' => array(
                    'BwatchController.index' => 'Bağlı hesaplar',
                    'BwatchController.create' => 'Hesap ekleme',
                    'BwatchController.edit' => 'Hesap düzenleme',
                    'BwatchController.destroy' => 'Hesap silme',
                    'BwatchController.run' => 'Hesap başlatma',
                    'BwatchController.pause' => 'Hesap duraklatma',
                    
                )
            ),
            'bwrules' => array(
                'active' => true,
                'label' => 'BW Kuralları',
                'actions' => array(
                    'BwrulesController.show' => 'Kurallar listesi',
                    'BwrulesController.create' => 'Kural ekleme',
                    'BwrulesController.edit' => 'Kural düzenleme',
                    'BwrulesController.destroy' => 'Kural silme',
                    'BwrulesController.bwQueryCall' => 'BW Query İşlemleri',
                    'BwrulesController.run' => 'Kural başlatma',
                    'BwrulesController.pause' => 'Kural duraklatma',
                )
            ),
            'sentimental' => array(
                'active' => true,
                'label' => 'Sentimental izinleri',
                'actions' => array(
                    'SentimentalController.index' => 'Sentimental listesi',
                    'SentimentalController.create' => 'Sentimental oluşturma',
                    'SentimentalController.edit' => 'Sentimental düzenleme',
                    'SentimentalController.destroy' => 'Sentimental Silme',
                )
            ),
            'filter' => array(
                'active' => true,
                'label' => 'Filtre izinleri',
                'actions' => array(
                    'FilterController.index' => 'Filtre listesi',
                    'FilterController.create' => 'Filtre oluşturma',
                    'FilterController.edit' => 'Filtre düzenleme',
                    'FilterController.destroy' => 'Filtre Silme',
                    'FilterController.tags' => 'Ajax Filtreler',
                )
            ),
            'domain' => array(
                'active' => true,
                'label' => 'Domain izinleri',
                'actions' => array(
                    'DomainController.index' => 'Domain listesi',
                    'DomainController.create' => 'Domain oluşturma',
                    'DomainController.edit' => 'Domain düzenleme',
                    'DomainController.destroy' => 'Domain Silme',
                )
            ),
            'statistics' => array(
                'active' => true,
                'label' => 'İstatistik izinleri',
                'actions' => array(
                    'StatisticsController.index' => 'İstatistikler',
                    'StatisticsController.analysis' => 'İstatistik Bilgileri',
                    'StatisticsController.chart' => 'İstatistik Chart Bilgileri',
                )
            ),
            'ticket' => array(
                'active' => true,
                'label' => 'Talep izinleri',
                'actions' => array(
                    'TicketController.index' => 'Talep listesi',
                    'TicketController.create' => 'Talep oluşturma',
                    'TicketController.edit' => 'Talep düzenleme',
                    'TicketController.destroy' => 'Talep Silme',
                    'TicketController.show' => 'Talep göster',
                    'TicketController.reply' => 'Talep cevapla',
                )
            ),
            'tag' => array(
                'active' => true,
                'label' => 'Kelime Kategorileri',
                'actions' => array(
                    'TagController.index' => 'Kelime kategori listesi',
                    'TagController.create' => 'Kelime kategori oluşturma',
                    'TagController.edit' => 'Kelime kategori düzenleme',
                    'TagController.destroy' => 'Kelime kategori silme',
                )
            ),
            'tagupload' => array(
                'active' => true,
                'label' => 'Kelime Kategori Dosyaları',
                'actions' => array(
                    'TagUploadController.show' => 'Kelime listesi',
                    'TagUploadController.create' => 'Kelime dosyası oluşturma',
                    'TagUploadController.edit' => 'Kelime düzenleme',
                    'TagUploadController.updateAjaxTag' => 'Kelime hızlı güncelleme',
                    'TagUploadController.add' => 'Kelime ekleme',
                    'TagUploadController.delete' => 'Kelime silme',
                    'TagUploadController.addTag' => 'Listelerden kelime ekleme',
                    'TagUploadController.removeTag' => 'Listelerden kelime silme',
                )
            ),
            'home' => array(
                'active' => true,
                'label' => 'Öğret izinleri',
                'actions' => array(
                    'HomeController.index' => 'Öğrenme sayfası',
                    'HomeController.addTraine' => 'Eğitim',
                    'HomeController.showAnalysis' => 'Analiz Sonuçları',
                    'HomeController.getLearned' => 'Öğrenme Sonuçları',
                )
            ),
            'tweet' => array(
                'active' => false,
                'label' => 'Tweet izinleri',
                'actions' => array(
                    'TweetController.index' => 'Tweet listesi',
                    'TweetController.user' => 'Kullanıcı Tweetleri',
                    'TweetController.getTweets' => 'Ajax tweet araması',
                    'TweetController.getFeeds' => 'Ajax kullanıcı tweetleri',
                )
            ),
            'source' => array(
                'active' => true,
                'label' => 'Kaynak Kategorileri',
                'actions' => array(
                    'SourceController.index' => 'Kaynak listesi',
                    'SourceController.create' => 'Kaynak oluşturma',
                    'SourceController.edit' => 'Kaynak düzenleme',
                    'SourceController.destroy' => 'Kaynak silme',
                )
            ),
            'upload' => array(
                'active' => true,
                'label' => 'Kaynak Dosyaları',
                'actions' => array(
                    'UploadController.show' => 'Yükleme listesi',
                    //'UploadController.index' => 'Yükleme Dosyaları listesi',
                    'UploadController.create' => 'Dosyası Oluşturma',
                    'UploadController.edit' => 'Dosyası Düzenleme',
                    'UploadController.destroy' => 'Dosyası Silme',
                )
            ),
            'comment' => array(
                'active' => true,
                'label' => 'Yorum izinleri',
                'actions' => array(
                    'CommentController.index' => 'Yorum listesi',
                    'CommentController.publish' => 'Yorumlar yayınlama',
                )
            ),
            'user_log' => array(
                'active' => true,
                'label' => 'Günlük (Log) izinleri',
                'actions' => array(
                    'UserLogController.index' => 'Kullanıcı günlükleri',
                    'UserLogController.show' => 'Günlük detayı',
                    'UserLogController.userlog' => 'Kişi Günlük dökümü',
                    'UserLogController.updatelogs' => 'Uygulama Versiyon Güncellemeleri',
                )
            ),
            'closure' => array(
                'active' => true,
                'label' => 'Closure izinleri',
                'actions' => array(
                    'Closure' => 'Tag filtreleri',
                )
            ),
        )
    )
);
