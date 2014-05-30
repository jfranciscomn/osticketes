<?php
/*********************************************************************
    index.php

    Helpdesk landing page. Please customize it to fit your needs.

    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2013 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/
require('client.inc.php');
$section = 'home';
require(CLIENTINC_DIR.'header.inc.php');
?>
<div id="landing_page">
    <?php
    if($cfg && ($page = $cfg->getLandingPage()))
        echo $page->getBodyWithImages();
    else
        echo  '<h1>Bienvenido al Centro de Atencion de C4i Sinaloa</h1>';
    ?>
    <div id="new_ticket">
        <h3>Abrir un nuevo Ticket</h3>
        <br>
        <div> Por favor provea la mayor cantidad de detalles posibles para asistirlo de la mejor manera posible.</div>
        <p>
            <a href="open.php" class="green button">Abrir un Nuevo Ticket</a>
        </p>
    </div>

    <div id="check_status">
        <h3>Revisar el Estado de un Ticket</h3>
        <br>
        <div>Puede revisar el estado de todas sus solicitudes de apoyo que nos ha realizado.
        </div>
        <p>
            <a href="view.php" class="blue button">Revisar el Estado de un Ticket</a>
        </p>
    </div>
</div>
<div class="clear"></div>
<?php
if($cfg && $cfg->isKnowledgebaseEnabled()){
    //FIXME: provide ability to feature or select random FAQs ??
?>
<p>Be sure to browse our <a href="kb/index.php">Frequently Asked Questions (FAQs)</a>, before opening a ticket.</p>
</div>
<?php
} ?>
<?php require(CLIENTINC_DIR.'footer.inc.php'); ?>
