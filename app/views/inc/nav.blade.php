<nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
    <div class="container-fluid">
        <div class="navbar-header">
            <a href="/" class="navbar-brand">{{ Account::find(Auth::user()->account_id)->name}}</a>
            <button class="navbar-toggle" type="button" data-toggle="collapse" data-target="#navbar-main">
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
        </div>
        <div class="navbar-collapse collapse" id="navbar-main">
            <ul class="nav navbar-nav">
                <!--                <li class="dropdown">-->
                <!--                    <a href="javascript:void(0)" class="dropdown-toggle" data-toggle="dropdown"><b class="caret"></b> Akış</a>
                                    <ul class="dropdown-menu" role="menu">-->
                <li {{ Request::is('comments')  ? 'class="active"' : '' }} >{{ HTML::link('comments', 'Yorum Akışı', array()) }}</li>
                <!--                    </ul>-->
                <!--                </li>-->
                <li {{ Request::is('tag') ? 'class="active"' : '' }} >{{ HTML::link('tag', 'Kelimeler', array()) }}</li>
                <li {{ Request::is('sentimental') ? 'class="active"' : '' }} >{{ HTML::link('sentimental', 'Sentimental', array()) }}</li>
                <!--                <li {{ Request::is('bayes') ? 'class="active"' : '' }} >{{ HTML::link('/', 'Api Test', array()) }}</li>-->
                <li {{ Request::is('updatelogs') ? 'class="active"' : '' }} >{{ HTML::link('updatelogs', 'Güncellemeler', array()) }}</li>
                <!--                <li {{ Request::is('statistics') ? 'class="active"' : '' }} >{{ HTML::link('statistics', 'İstatistikler', array()) }}</li>-->
            </ul>
            <ul class="nav navbar-nav navbar-right">



                <li>{{ HTML::link('auth/logout', Auth::user()->email, array()) }}</li>
                <li class="dropdown">
                    <a href="javascript:void(0)" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-cog"></i> Ayarlar</a>
                    <ul class="dropdown-menu" role="menu">
                        <li>{{ HTML::link('account', 'Hesap', array()) }}</li>
                        <li>{{ HTML::link('invoice', 'Fatura', array()) }}</li>
                        <li class="divider"></li>
                        @if (Auth::user()->role == 'super' || Auth::user()->role == 'admin')
                        <li>{{ HTML::link('user', 'Kullanıcılar', array()) }}</li>
                        <li class="divider"></li>
                        <li>{{ HTML::link('source', 'Kaynaklar', array()) }}</li>
                        <li>{{ HTML::link('tag', 'Kelimeler', array()) }}</li>
                        <li>{{ HTML::link('domain', 'Domainler', array()) }}</li>
                        <li class="divider"></li>
                        <li class="list-group-item-info">Eklentiler


                        <li class="list-unstyled">{{ HTML::link('bwatch', 'Brandwatch', array()) }}</li></li> 
                <li class="divider"></li>
                @endif
                <li>{{ HTML::link('auth/changepassword', 'Şifre Değiştir', array()) }}</li>
                <li class="divider"></li>
                <li>{{ HTML::link('userlogs', 'Günlükler', array()) }}</li>
                <li class="divider"></li>
                <li>{{ HTML::link('ticket', 'Talepler', array()) }}</li>
                <li class="divider"></li>
                @if(is_super_admin(Auth::user()->id))
                <li>{{ HTML::link('cacheflush', 'Cache Sil', array()) }}</li>
                <li class="divider"></li>
                @endif
                <li>{{ HTML::link('auth/logout', 'Çıkış', array()) }}</li>
            </ul>
            </li>
            </ul>
        </div>
    </div>
</nav>