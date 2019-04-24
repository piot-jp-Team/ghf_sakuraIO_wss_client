# ghf_sakuraIO_wss_client
## 概要：phpのratchet/pawlを使ってsakura.ioのwebsocket clientとして動作する。  
node.jsでなくphpで作ってしまった。  
wssセッションが切れると復帰するためのプロセス監視用シェルスクリプトを定期実行。   
さくらＶＰＳ（centos7、php7、nginx）で動作。  
ghf_webapp用アラートメールトリガーも含む。 

## ディレクトリ構成

```

├── alertmail.sh         アラートメール用スクリプトghf_webappのアラートメールトリガー用 
├── alertmaillog.txt     上記ログ 
├── keepalive.txt        sakura.ioからのkeepaliveメッセージ格納用。sakura.ioは定期的に送信してくるの保証はしていないとのこと（セミナー時回答）。
├── sakura_wss
│   ├── composer.json
│   ├── log_Continuously.txt   wssログ（たまに手動でローテーションする必要がある）
│   ├── skws_Continuously.php  wssスクリプト本体  
│   ├── composer.lock         「composer install」実行後生成  
│   └── vendor                「composer install」実行後生成   
│        ├── aaaa
│        ├── bbb
│        └── ccccc
├── skws_Contin.sh              skwstimeexpire.shのwssプロセス監視で反応がなくなったときのwssスクリプト本体起動用。
├── skwstimeexpire.sh           wssプロセスの監視
└── wssfileexplog.txt           上記wssプロセス監視で反応がなくなったときのログ

```

## インストール  

### インストールの方法  

#### 各設定情報変更  
##### ディレクトリ変更/xxxx/yyyを配置するディテクトリに変更してください  
skwstimeexpire.sh  3行目    FILETIME=`date +"%y%m%d%H%M" -r /xxxx/yyy/sakura_wss/log_Continuously.txt`  
skwstimeexpire.sh  6行目    メール送信先test@test.com　を変更  
skwstimeexpire.sh 12行目    /xxxx/yyy/skws_Contin.sh  
alertmail.sh       3行目    cd /xxxx/yyy/ghf_webapp    ghf_webappの配置先です。  
skws_Contin.sh     3行目    nohup bash -c "php /xxxx/yyy/sakura_wss/skws_Continuously.php >> /xxxx/yyy/sakura_wss/log_Continuously.txt" > /xxxx/yyy/nohupresult.log &  

##### 接続先変更  
sakura_wss/skws_Continuously.php  8行目　wss://api.sakura.io・・・・sakura.ioのwebsocketURLに置き換えて下さい。  
sakura_wss/skws_Continuously.php 14行目　使用するmysql接続情報に置き換えて下さい。  

##### composerインストール  
cd sakura_wss  
composer install  

##### cron実行時のログ書込み先ファイル作成  
cd /xxxx/yyy  
touch wssfileexplog.txt  
touch alertmaillog.txt  

##### wssプロセスの監視、及びアラートメールのcronスケジューラ設定  
cronの場合、crontab -e  
*/5 * * * * /xxxx/yyy/skwstimeexpire.sh >> /xxxx/yyy/wssfileexplog.txt  
*/3 * * * * /xxxx/yyy/alertmail.sh >> /xxxx/yyy/alertmaillog.txt  


## 機能説明  
##### skwstimeexpire.sh  
wss通信ログをチェックし更新日付が10分以上遅れていれば、メールを送信し  
skws_Continuouslyプロセスが消えていれば、skws_Contin.shを起動し  
skws_Continuously.phpをバックグランドで起動します。  
##### alertmail.sh  
ghf_webapp用で、laravel notificationメール用のデータチェックトリガーで条件に合致しているデータがあるとメールする定期実行用のスクリプトです。  
