<?php
if(!defined('OSTCLIENTINC')) die('Access Denied');

$email=Format::input($_POST['lemail']?$_POST['lemail']:$_GET['e']);
$ticketid=Format::input($_POST['lticket']?$_POST['lticket']:$_GET['t']);
?>
<h1>Revisar Estatus del Ticket</h1>
<p>Por Favor proporcione los siguientes datos, para consultar el estado de sus peticiones.</p>
<form action="login.php" method="post" id="clientLogin">
    <?php csrf_token(); ?>
    <strong><?php echo Format::htmlchars($errors['login']); ?></strong>
    <br>
    <div>
        <label for="email">Correo Electronico:</label>
        <input id="email" type="text" name="lemail" size="30" value="<?php echo $email; ?>">
    </div>
    <div>
        <label for="ticketno">Ticket #:</label>
        <input id="ticketno" type="text" name="lticket" size="16" value="<?php echo $ticketid; ?>"></td>
    </div>
    <p>
        <input class="btn" type="submit" value="Email Access Link">
    </p>
</form>
<br>
<p>
Si el ticket no esta registrado, por favor <a href="open.php"> abra un nuevo ticket</a>.
</p>
