<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta http-equiv="cache-control" content="no-cache" />
    <meta http-equiv="pragma" content="no-cache" />
    <title><?php echo ($ost && ($title=$ost->getPageTitle()))?$title:'osTicket :: Staff Control Panel'; ?></title>
    <!--[if IE]>
    <style type="text/css">
        .tip_shadow { display:block !important; }
    </style>
    <![endif]-->
    <script type="text/javascript" src="<?php echo ROOT_PATH; ?>js/jquery-1.8.3.min.js?4b0877c"></script>
    <script type="text/javascript" src="<?php echo ROOT_PATH; ?>js/jquery-ui-1.10.3.custom.min.js?4b0877c"></script>
    <script type="text/javascript" src="../js/jquery.multifile.js?4b0877c"></script>
    <script type="text/javascript" src="./js/tips.js?4b0877c"></script>
    <script type="text/javascript" src="<?php echo ROOT_PATH; ?>js/redactor.min.js?4b0877c"></script>
    <script type="text/javascript" src="<?php echo ROOT_PATH; ?>js/redactor-osticket.js?4b0877c"></script>
    <script type="text/javascript" src="<?php echo ROOT_PATH; ?>js/redactor-fonts.js?4b0877c"></script>
    <script type="text/javascript" src="./js/bootstrap-typeahead.js?4b0877c"></script>
    <script type="text/javascript" src="./js/scp.js?4b0877c"></script>
    <link rel="stylesheet" href="<?php echo ROOT_PATH ?>css/thread.css?4b0877c" media="all"/>
    <link rel="stylesheet" href="./css/scp.css?4b0877c" media="all"/>
    <link rel="stylesheet" href="<?php echo ROOT_PATH; ?>css/redactor.css?4b0877c" media="screen"/>
    <link rel="stylesheet" href="./css/typeahead.css?4b0877c" media="screen"/>
    <link type="text/css" href="<?php echo ROOT_PATH; ?>css/ui-lightness/jquery-ui-1.10.3.custom.min.css?4b0877c"
         rel="stylesheet" media="screen" />
     <link type="text/css" rel="stylesheet" href="<?php echo ROOT_PATH; ?>css/font-awesome.min.css?4b0877c"/>
    <!--[if IE 7]>
    <link rel="stylesheet" href="<?php echo ROOT_PATH; ?>css/font-awesome-ie7.min.css?4b0877c"/>
    <![endif]-->
    <link type="text/css" rel="stylesheet" href="./css/dropdown.css?4b0877c"/>
    <script type="text/javascript" src="./js/jquery.dropdown.js?4b0877c"></script>
    <?php
    if($ost && ($headers=$ost->getExtraHeaders())) {
        echo "\n\t".implode("\n\t", $headers)."\n";
    }
    ?>
</head>
<body>
<div id="container">
    <?php
    if($ost->getError())
        echo sprintf('<div id="error_bar">%s</div>', $ost->getError());
    elseif($ost->getWarning())
        echo sprintf('<div id="warning_bar">%s</div>', $ost->getWarning());
    elseif($ost->getNotice())
        echo sprintf('<div id="notice_bar">%s</div>', $ost->getNotice());
    ?>
    <div id="header">
        <a href="index.php" id="logo">osTicket - Customer Support System</a>
        <p id="info">Welcome, <strong><?php echo $thisstaff->getFirstName(); ?></strong>
           <?php
            if($thisstaff->isAdmin() && !defined('ADMINPAGE')) { ?>
            | <a href="admin.php">Admin Panel</a>
            <?php }else{ ?>
            | <a href="index.php">Staff Panel</a>
            <?php } ?>
            | <a href="profile.php">My Preferences</a>
            | <a href="logout.php?auth=<?php echo $ost->getLinkToken(); ?>">Log Out</a>
        </p>
    </div>
    <ul id="nav">
        <?php
        if(($tabs=$nav->getTabs()) && is_array($tabs)){
            foreach($tabs as $name =>$tab) {
                echo sprintf('<li class="%s"><a href="%s">%s</a>',$tab['active']?'active':'inactive',$tab['href'],$tab['desc']);
                if(!$tab['active'] && ($subnav=$nav->getSubMenu($name))){
                    echo "<ul>\n";
                    foreach($subnav as $k => $item) {
                        if (!($id=$item['id']))
                            $id="nav$k";

                        echo sprintf('<li><a class="%s" href="%s" title="%s" id="%s">%s</a></li>',
                                $item['iconclass'], $item['href'], $item['title'], $id, $item['desc']);
                    }
                    echo "\n</ul>\n";
                }
                echo "\n</li>\n";
            }
        } ?>
    </ul>
    <ul id="sub_nav">
        <?php
        if(($subnav=$nav->getSubMenu()) && is_array($subnav)){
            $activeMenu=$nav->getActiveMenu();
            if($activeMenu>0 && !isset($subnav[$activeMenu-1]))
                $activeMenu=0;
            foreach($subnav as $k=> $item) {
                if($item['droponly']) continue;
                $class=$item['iconclass'];
                if ($activeMenu && $k+1==$activeMenu
                        or (!$activeMenu
                            && (strpos(strtoupper($item['href']),strtoupper(basename($_SERVER['SCRIPT_NAME']))) !== false
                                or ($item['urls']
                                    && in_array(basename($_SERVER['SCRIPT_NAME']),$item['urls'])
                                    )
                                )))
                    $class="$class active";
                if (!($id=$item['id']))
                    $id="subnav$k";

                echo sprintf('<li><a class="%s" href="%s" title="%s" id="%s">%s</a></li>',
                        $class, $item['href'], $item['title'], $id, $item['desc']);
            }
        }
        ?>
    </ul>
    <div id="content">
        <?php if($errors['err']) { ?>
            <div id="msg_error"><?php echo $errors['err']; ?></div>
        <?php }elseif($msg) { ?>
            <div id="msg_notice"><?php echo $msg; ?></div>
        <?php }elseif($warn) { ?>
            <div id="msg_warning"><?php echo $warn; ?></div>
        <?php } ?>
