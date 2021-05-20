<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?=$this->url?''.ucwords(str_replace(['-','/'],' ',$this->url)).' · ':''?><?=$this->config['app_name']?></title>
    <link rel="stylesheet" href="<?=BASE_URL?>/static/<?=$this->config['theme']?>.css">
    <link rel="shortcut icon" href="<?=BASE_URL?>/static/picowiki-favicon.png" type="image/png">
	<?=$this->event('template_header', $this)?>
</head>
<body>

<div id="main" class="main">
