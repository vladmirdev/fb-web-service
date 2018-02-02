<?php
/**
 * Created by PhpStorm.
 * User: zhek
 * Date: 24.10.17
 * Time: 15:28
 */

/* @var $this \yii\web\View view component instance */
/* @var $message \yii\mail\MessageInterface the message being composed */
/* @var $content string main view render result */

preg_match_all('/"%inline%(.*[^"])"/', $content, $matches);

if(sizeof($matches) > 0 && sizeof($matches[0]) > 0) {

    $original = $matches[0];
    $clean = $matches[1];

    foreach($original as $index => $path) {
        $content = str_replace($path, $message->embed(Yii::getAlias('@app/web') . $clean[$index]), $content);
    }
}

?>
<?php $this->beginPage() ?>
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=<?= Yii::$app->charset ?>" />
        <style type="text/css">
            .footer {
                font-size: 12px;
                color: #dadada;
                background-color: #ffffff;
            }
            .content {
                background-color: #f7f7f7;
                padding: 30px;
                border-top: 1px solid #e9e9e9;
                border-bottom: 1px solid #e9e9e9;
                font-family: 'PT Sans',Helvetica Neue,Helvetica,Lucida Grande,tahoma,verdana,arial,sans-serif;
            }
            .footer {
                padding: 15px;
            }
        </style>
        <?php $this->head() ?>
    </head>
    <body>
    <?php $this->beginBody() ?>
    <div class="header">
        <img src="<?= $message->embed(Yii::getAlias('@app/web') . '/images/logo_email.png'); ?>">
    </div>
    <div class="content">
        <?= $content ?>
    </div>
    <div class="footer">
        <div style="width: 50%; float:left">
            <?= Yii::$app->name ?> LLC
        </div>
        <div style="width: 50%; float:right; text-align: right">
            <?= date('Y'); ?> - All Rights Reserved
        </div>
    </div>
    <?php $this->endBody() ?>
    </body>
    </html>
<?php $this->endPage() ?>