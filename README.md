# ghf_sakuraIO_wss_client
## 概要：phpのratchet/pawlを使ってsakura.ioのwebsocket clientとして動作する。  
　　　プロセス監視用シェルスクリプトを定期実行。  
　　　ghf_webapp用アラートメールトリガーも含む。   
      さくらＶＰＳ（centos7、php7、nginx）で動作。　

## ディレクトリ構成
　　　以下の説明は、/xxxx/yyy の配下に設置するものとします  
--------ディレクトリ構成------------------------------  

|--alertmail.sh          アラートメール用スクリプトpiot_webappのアラートメールトリガー用  
|--alertmaillog.txt      上記ログ  
|--skwstimeexpire.sh     wssプロセスの監視  
|--keepalive.txt         sakura.ioからのkeepaliveメッセージ格納用。sakura.ioは定期的に送信してくるの保証はしていないとのこと（セミナー時回答）。  
|--skws_Contin.sh        skwstimeexpire.shのwssプロセス監視で反応がなくなったときのwssスクリプト本体起動用。  
|--sakura_wss--  
	|--log_Continuously.txt    wssログ（たまに手動でローテーションする必要がある）  
	|--skws_Continuously.php   wssスクリプト本体  
	|--composer.json  
	|--composer.lock        以下「composer install」実行後自動生成  
	|--vendor--  
		|-- aaaa  
		|-- bbbb  
		|-- cccc  

## インストール  

### インストールの方法  

#### 各設定情報変更  
##### ディレクトリ変更/xxxx/yyyを配置するディテクトリに変更してください  
skwstimeexpire.sh  3行目    FILETIME=`date +"%y%m%d%H%M" -r /xxxx/yyy/sakura_wss/log_Continuously.txt`  
skwstimeexpire.sh 12行目    /xxxx/yyy/skws_Contin.sh  
alertmail.sh       3行目    cd /xxxx/yyy/laravel_chart_dir  piot_webappの配置先です。  
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
skwstimeexpire.shは、wss通信ログをチェックし更新日付が10分以上遅れていれば、メールを送信し  
skws_Continuouslyプロセスが消えていれば、skws_Contin.shを起動し  
skws_Continuously.phpをバックグランドで起動します。  
alertmail.shは、ghf_webapp用で、laravel notificationメール用のデータチェックトリガーで条件に合致しているデータがあるとメールする定期実行用のスクリプトです。  
