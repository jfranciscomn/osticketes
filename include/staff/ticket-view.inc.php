<?php
//Note that ticket obj is initiated in tickets.php.
if(!defined('OSTSCPINC') || !$thisstaff || !is_object($ticket) || !$ticket->getId()) die('Invalid path');

//Make sure the staff is allowed to access the page.
if(!@$thisstaff->isStaff() || !$ticket->checkStaffAccess($thisstaff)) die('Access Denied');

//Re-use the post info on error...savekeyboards.org (Why keyboard? -> some people care about objects than users!!)
$info=($_POST && $errors)?Format::input($_POST):array();

//Auto-lock the ticket if locking is enabled.. If already locked by the user then it simply renews.
if($cfg->getLockTime() && !$ticket->acquireLock($thisstaff->getId(),$cfg->getLockTime()))
    $warn.='Unable to obtain a lock on the ticket';

//Get the goodies.
$dept  = $ticket->getDept();  //Dept
$staff = $ticket->getStaff(); //Assigned or closed by..
$user  = $ticket->getOwner(); //Ticket User (EndUser)
$team  = $ticket->getTeam();  //Assigned team.
$sla   = $ticket->getSLA();
$lock  = $ticket->getLock();  //Ticket lock obj
$id    = $ticket->getId();    //Ticket ID.

//Useful warnings and errors the user might want to know!
if($ticket->isAssigned() && (
            ($staff && $staff->getId()!=$thisstaff->getId())
         || ($team && !$team->hasMember($thisstaff))
        ))
    $warn.='&nbsp;&nbsp;<span class="Icon assignedTicket">Ticket is assigned to '.implode('/', $ticket->getAssignees()).'</span>';
if(!$errors['err'] && ($lock && $lock->getStaffId()!=$thisstaff->getId()))
    $errors['err']='This ticket is currently locked by '.$lock->getStaffName();
if(!$errors['err'] && ($emailBanned=TicketFilter::isBanned($ticket->getEmail())))
    $errors['err']='Email is in banlist! Must be removed before any reply/response';

$unbannable=($emailBanned) ? BanList::includes($ticket->getEmail()) : false;

if($ticket->isOverdue())
    $warn.='&nbsp;&nbsp;<span class="Icon overdueTicket">Marked overdue!</span>';

?>
<table width="940" cellpadding="2" cellspacing="0" border="0">
    <tr>
        <td width="50%" class="has_bottom_border">
             <h2><a href="tickets.php?id=<?php echo $ticket->getId(); ?>"
             title="Reload"><i class="icon-refresh"></i> Ticket #<?php echo $ticket->getNumber(); ?></a></h2>
        </td>
        <td width="50%" class="right_align has_bottom_border">
            <?php
            if ($thisstaff->canBanEmails()
                    || $thisstaff->canEditTickets()
                    || ($dept && $dept->isManager($thisstaff))) { ?>
            <span class="action-button" data-dropdown="#action-dropdown-more">
                <span ><i class="icon-cog"></i> Mas</span>
                <i class="icon-caret-down"></i>
            </span>
            <?php
            } ?>
            <?php if($thisstaff->canDeleteTickets()) { ?>
                <a id="ticket-delete" class="action-button" href="#delete"><i class="icon-trash"></i> Eliminar</a>
            <?php } ?>
            <?php
            if($thisstaff->canCloseTickets()) {
                if($ticket->isOpen()) {?>
                <a id="ticket-close" class="action-button" href="#close"><i class="icon-remove-circle"></i> Cerrar</a>
                <?php
                } else { ?>
                <a id="ticket-reopen" class="action-button" href="#reopen"><i class="icon-undo"></i> Reabrir</a>
                <?php
                } ?>
            <?php
            } ?>
            <?php
            if($thisstaff->canEditTickets()) { ?>
                <a class="action-button" href="tickets.php?id=<?php echo $ticket->getId(); ?>&a=edit"><i class="icon-edit"></i> Editar</a>
            <?php
            } ?>
            <?php
            if($ticket->isOpen() && !$ticket->isAssigned() && $thisstaff->canAssignTickets()) {?>
                <a id="ticket-claim" class="action-button" href="#claim"><i class="icon-user"></i> Reclamar</a>

            <?php
            }?>
            <span class="action-button" data-dropdown="#action-dropdown-print">
                <a id="ticket-print" href="tickets.php?id=<?php echo $ticket->getId(); ?>&a=print"><i class="icon-print"></i> Imprimir</a>
                <i class="icon-caret-down"></i>
            </span>
            <div id="action-dropdown-print" class="action-dropdown anchor-right">
              <ul>
                 <li><a target="_blank" href="tickets.php?id=<?php echo $ticket->getId(); ?>&a=print&notes=0"><i
                 class="icon-file-alt"></i> Ticket </a>
                 <li><a target="_blank" href="tickets.php?id=<?php echo $ticket->getId(); ?>&a=print&notes=1"><i
                 class="icon-file-text-alt"></i> Thread + Notas Internas</a>
              </ul>
            </div>
            <div id="action-dropdown-more" class="action-dropdown anchor-right">
              <ul>
                <?php
                 if($thisstaff->canEditTickets()) { ?>
                    <li><a class="change-user" href="#tickets/<?php echo $ticket->getId(); ?>/change-user"><i class="icon-user"></i> Cambiar Cliente</a></li>
                <?php
                 }
                if($ticket->isOpen() && ($dept && $dept->isManager($thisstaff))) {

                    if($ticket->isAssigned()) { ?>
                        <li><a id="ticket-release" href="#release"><i class="icon-user"></i> Desasignar Ticket</a></li>
                    <?php
                    }

                    if(!$ticket->isOverdue()) { ?>
                        <li><a id="ticket-overdue" href="#overdue"><i class="icon-bell"></i> Marcar como Retrasado</a></li>
                    <?php
                    }

                    if($ticket->isAnswered()) { ?>
                        <li><a id="ticket-unanswered" href="#unanswered"><i class="icon-circle-arrow-left"></i> Marcar como no Contestado</a></li>
                    <?php
                    } else { ?>
                        <li><a id="ticket-answered" href="#answered"><i class="icon-circle-arrow-right"></i> Marcar como Contestado</a></li>
                    <?php
                    }
                }

                if($thisstaff->canBanEmails()) {
                     if(!$emailBanned) {?>
                        <li><a id="ticket-banemail" href="#banemail"><i class="icon-ban-circle"></i> Ban Email (<?php echo $ticket->getEmail(); ?>)</a></li>
                <?php
                     } elseif($unbannable) { ?>
                        <li><a id="ticket-banemail" href="#unbanemail"><i class="icon-undo"></i> Unban Email (<?php echo $ticket->getEmail(); ?>)</a></li>
                    <?php
                     }
                }?>
              </ul>
            </div>
        </td>
    </tr>
</table>
<table class="ticket_info" cellspacing="0" cellpadding="0" width="940" border="0">
    <tr>
        <td width="50">
            <table border="0" cellspacing="" cellpadding="4" width="100%">
                <tr>
                    <th width="100">Estatus:</th>
                    <td><?php echo ucfirst($ticket->getStatus()); ?></td>
                </tr>
                <tr>
                    <th>Prioridad:</th>
                    <td><?php echo $ticket->getPriority(); ?></td>
                </tr>
                <tr>
                    <th>Departamento:</th>
                    <td><?php echo Format::htmlchars($ticket->getDeptName()); ?></td>
                </tr>
                <tr>
                    <th>Fecha de Creacion:</th>
                    <td><?php echo Format::db_datetime($ticket->getCreateDate()); ?></td>
                </tr>
            </table>
        </td>
        <td width="50%" style="vertical-align:top">
            <table border="0" cellspacing="" cellpadding="4" width="100%">
                <tr>
                    <th width="100">Cliente:</th>
                    <td><a href="#tickets/<?php echo $ticket->getId(); ?>/user"
                        onclick="javascript:
                            $.userLookup('ajax.php/tickets/<?php echo $ticket->getId(); ?>/user',
                                    function (user) {
                                        $('#user-'+user.id+'-name').text(user.name);
                                        $('#user-'+user.id+'-email').text(user.email);
                                        $('#user-'+user.id+'-phone').text(user.phone);
                                        $('select#emailreply option[value=1]').text(user.name+' <'+user.email+'>');
                                    });
                            return false;
                            "><i class="icon-user"></i> <span id="user-<?php echo $ticket->getOwnerId(); ?>-name"
                            ><?php echo Format::htmlchars($ticket->getName());
                        ?></span></a>
                        <?php
                        if($user) {
                            echo sprintf('&nbsp;&nbsp;<a href="tickets.php?a=search&uid=%d" title="Related Tickets" data-dropdown="#action-dropdown-stats">(<b>%d</b>)</a>',
                                    urlencode($user->getId()), $user->getNumTickets());
                        ?>
                            <div id="action-dropdown-stats" class="action-dropdown anchor-right">
                                <ul>
                                    <?php
                                    if(($open=$user->getNumOpenTickets()))
                                        echo sprintf('<li><a href="tickets.php?a=search&status=open&uid=%s"><i class="icon-folder-open-alt"></i> %d Tickets Abiertos</a></li>',
                                                $user->getId(), $open);
                                    if(($closed=$user->getNumClosedTickets()))
                                        echo sprintf('<li><a href="tickets.php?a=search&status=closed&uid=%d"><i class="icon-folder-close-alt"></i> %d Tickets Cerrados</a></li>',
                                                $user->getId(), $closed);
                                    ?>
                                    <li><a href="tickets.php?a=search&uid=<?php echo $ticket->getOwnerId(); ?>"><i class="icon-double-angle-right"></i> Todos los Tickets</a></li>
                                </u>
                            </div>
                    <?php
                        }
                    ?>
                    </td>
                </tr>
                <tr>
                    <th>Correo:</th>
                    <td>
                        <span id="user-<?php echo $ticket->getOwnerId(); ?>-email"><?php echo $ticket->getEmail(); ?></span>
                    </td>
                </tr>
                <tr>
                    <th>Telefono:</th>
                    <td>
                        <span id="user-<?php echo $ticket->getOwnerId(); ?>-phone"><?php echo $ticket->getPhoneNumber(); ?></span>
                    </td>
                </tr>
                <tr>
                    <th>Fuente:</th>
                    <td><?php
                        echo Format::htmlchars($ticket->getSource());

                        if($ticket->getIP())
                            echo '&nbsp;&nbsp; <span class="faded">('.$ticket->getIP().')</span>';
                        ?>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
<br>
<table class="ticket_info" cellspacing="0" cellpadding="0" width="940" border="0">
    <tr>
        <td width="50%">
            <table cellspacing="0" cellpadding="4" width="100%" border="0">
                <?php
                if($ticket->isOpen()) { ?>
                <tr>
                    <th width="100">Asignado a:</th>
                    <td>
                        <?php
                        if($ticket->isAssigned())
                            echo Format::htmlchars(implode('/', $ticket->getAssignees()));
                        else
                            echo '<span class="faded">&mdash; Unassigned &mdash;</span>';
                        ?>
                    </td>
                </tr>
                <?php
                } else { ?>
                <tr>
                    <th width="100">Cerrado Por:</th>
                    <td>
                        <?php
                        if(($staff = $ticket->getStaff()))
                            echo Format::htmlchars($staff->getName());
                        else
                            echo '<span class="faded">&mdash; Unknown &mdash;</span>';
                        ?>
                    </td>
                </tr>
                <?php
                } ?>
                <tr>
                    <th>SLA Plan:</th>
                    <td><?php echo $sla?Format::htmlchars($sla->getName()):'<span class="faded">&mdash; none &mdash;</span>'; ?></td>
                </tr>
                <?php
                if($ticket->isOpen()){ ?>
                <tr>
                    <th>Fecha de Termino:</th>
                    <td><?php echo Format::db_datetime($ticket->getEstDueDate()); ?></td>
                </tr>
                <?php
                }else { ?>
                <tr>
                    <th>Fecha de Cierre:</th>
                    <td><?php echo Format::db_datetime($ticket->getCloseDate()); ?></td>
                </tr>
                <?php
                }
                ?>
            </table>
        </td>
        <td width="50%">
            <table cellspacing="0" cellpadding="4" width="100%" border="0">
                <tr>
                    <th width="100">Topico:</th>
                    <td><?php echo Format::htmlchars($ticket->getHelpTopic()); ?></td>
                </tr>
                <tr>
                    <th nowrap>Ultimo Mensaje:</th>
                    <td><?php echo Format::db_datetime($ticket->getLastMsgDate()); ?></td>
                </tr>
                <tr>
                    <th nowrap>Ultima Respuesta:</th>
                    <td><?php echo Format::db_datetime($ticket->getLastRespDate()); ?></td>
                </tr>
            </table>
        </td>
    </tr>
</table>
<br>
<table class="ticket_info" cellspacing="0" cellpadding="0" width="940" border="0">
<?php
$idx = 0;
foreach (DynamicFormEntry::forTicket($ticket->getId()) as $form) {
    // Skip core fields shown earlier in the ticket view
    // TODO: Rewrite getAnswers() so that one could write
    //       ->getAnswers()->filter(not(array('field__name__in'=>
    //           array('email', ...))));
    $answers = array_filter($form->getAnswers(), function ($a) {
        return !in_array($a->getField()->get('name'),
                array('email','subject','name','priority'));
        });
    if (count($answers) == 0)
        continue;
    ?>
        </tr><tr>
        <td colspan="2">
            <table cellspacing="0" cellpadding="4" width="100%" border="0">
            <?php foreach($answers as $a) {
                if (!($v = $a->display())) continue; ?>
                <tr>
                    <th width="100"><?php
    echo $a->getField()->get('label');
                    ?>:</th>
                    <td><?php
    echo $v;
                    ?></td>
                </tr>
                <?php } ?>
            </table>
        </td>
    <?php
    $idx++;
    } ?>
    </tr>
</table>
<div class="clear"></div>
<h2 style="padding:10px 0 5px 0; font-size:11pt;"><?php echo Format::htmlchars($ticket->getSubject()); ?></h2>
<?php
$tcount = $ticket->getThreadCount();
$tcount+= $ticket->getNumNotes();
?>
<ul id="threads">
    <li><a class="active" id="toggle_ticket_thread" href="#">Ticket (<?php echo $tcount; ?>)</a></li>
</ul>
<div id="ticket_thread">
    <?php
    $threadTypes=array('M'=>'message','R'=>'response', 'N'=>'note');
    /* -------- Messages & Responses & Notes (if inline)-------------*/
    $types = array('M', 'R', 'N');
    if(($thread=$ticket->getThreadEntries($types))) {
       foreach($thread as $entry) {
           if ($entry['body'] == '-')
               $entry['body'] = '(EMPTY)';
           ?>
        <table class="thread-entry <?php echo $threadTypes[$entry['thread_type']]; ?>" cellspacing="0" cellpadding="1" width="940" border="0">
            <tr>
                <th colspan="4" width="100%">
                <div>
                    <span style="display:inline-block"><?php
                        echo Format::db_datetime($entry['created']);?></span>
                    <span style="display:inline-block;padding-left:1em" class="faded title"><?php
                        echo Format::truncate($entry['title'], 100); ?></span>
                    <span style="float:right;white-space:no-wrap;display:inline-block">
                        <span style="vertical-align:middle;" class="textra"></span>
                        <span style="vertical-align:middle;"
                            class="tmeta faded title"><?php
                            echo Format::htmlchars($entry['name'] ?: $entry['poster']); ?></span>
                    </span>
                </div>
                </th>
            </tr>
            <tr><td colspan="4" class="thread-body" id="thread-id-<?php
                echo $entry['id']; ?>"><div><?php
                echo Format::viewableImages(Format::display($entry['body'])); ?></div></td></tr>
            <?php
            if($entry['attachments']
                    && ($tentry=$ticket->getThreadEntry($entry['id']))
                    && ($urls = $tentry->getAttachmentUrls())
                    && ($links=$tentry->getAttachmentsLinks())) {?>
            <tr>
                <td class="info" colspan="4"><?php echo $links; ?></td>
                <script type="text/javascript">
                    $(function() { showImagesInline(<?php echo
                        JsonDataEncoder::encode($urls); ?>); });
                </script>
            </tr>
            <?php
            }?>
        </table>
        <?php
        if($entry['thread_type']=='M')
            $msgId=$entry['id'];
       }
    } else {
        echo '<p>Error fetching ticket thread - get technical help.</p>';
    }?>
</div>
<div class="clear" style="padding-bottom:10px;"></div>
<?php if($errors['err']) { ?>
    <div id="msg_error"><?php echo $errors['err']; ?></div>
<?php }elseif($msg) { ?>
    <div id="msg_notice"><?php echo $msg; ?></div>
<?php }elseif($warn) { ?>
    <div id="msg_warning"><?php echo $warn; ?></div>
<?php } ?>

<div id="response_options">
    <ul class="tabs">
        <?php
        if($thisstaff->canPostReply()) { ?>
        <li><a id="reply_tab" href="#reply">Publicar Respuesta</a></li>
        <?php
        } ?>
        <li><a id="note_tab" href="#note">Pub. Nota Interna</a></li>
        <?php
        if($thisstaff->canTransferTickets()) { ?>
        <li><a id="transfer_tab" href="#transfer">Transferir de Dept.</a></li>
        <?php
        }

        if($thisstaff->canAssignTickets()) { ?>
        <li><a id="assign_tab" href="#assign"><?php echo $ticket->isAssigned()?'Reasignar Ticket':'Asignar Ticket'; ?></a></li>
        <?php
        } ?>
    </ul>
    <?php
    if($thisstaff->canPostReply()) { ?>
    <form id="reply" action="tickets.php?id=<?php echo $ticket->getId(); ?>#reply" name="reply" method="post" enctype="multipart/form-data">
        <?php csrf_token(); ?>
        <input type="hidden" name="id" value="<?php echo $ticket->getId(); ?>">
        <input type="hidden" name="msgId" value="<?php echo $msgId; ?>">
        <input type="hidden" name="a" value="reply">
        <span class="error"></span>
        <table style="width:100%" border="0" cellspacing="0" cellpadding="3">
           <tbody id="to_sec">
            <tr>
                <td width="120">
                    <label><strong>Para:</strong></label>
                </td>
                <td>
                    <?php
                    # XXX: Add user-to-name and user-to-email HTML ID#s
                    $to =sprintf('%s &lt;%s&gt;', $ticket->getName(), $ticket->getReplyToEmail());
                    $emailReply = (!isset($info['emailreply']) || $info['emailreply']);
                    ?>
                    <select id="emailreply" name="emailreply">
                        <option value="1" <?php echo $emailReply ?  'selected="selected"' : ''; ?>><?php echo $to; ?></option>
                        <option value="0" <?php echo !$emailReply ? 'selected="selected"' : ''; ?>
                            >&mdash;Do Not Email Reply&mdash;</option>
                    </select>
                </td>
            </tr>
            </tbody>
            <?php
            if(1) { //Make CC optional feature? NO, for now.
                ?>
            <tbody id="cc_sec"
                style="display:<?php echo $emailReply?  'table-row-group':'none'; ?>;">
             <tr>
                <td width="120">
                    <label><strong>Colaboradores:</strong></label>
                </td>
                <td>
                    <input type='checkbox' value='1' name="emailcollab" id="emailcollab"
                        <?php echo ((!$info['emailcollab'] && !$errors) || isset($info['emailcollab']))?'checked="checked"':''; ?>
                        style="display:<?php echo $ticket->getNumCollaborators() ? 'inline-block': 'none'; ?>;"
                        >
                    <?php
                    $recipients = 'Agregar';
                    if ($ticket->getNumCollaborators())
                        $recipients = sprintf('Recipients (%d of %d)',
                                $ticket->getNumActiveCollaborators(),
                                $ticket->getNumCollaborators());

                    echo sprintf('<span><a class="collaborators preview"
                            href="#tickets/%d/collaborators"><span id="recipients">%s</span></a></span>',
                            $ticket->getId(),
                            $recipients);
                   ?>
                </td>
             </tr>
            </tbody>
            <?php
            } ?>
            <tbody id="resp_sec">
            <?php
            if($errors['response']) {?>
            <tr><td width="120">&nbsp;</td><td class="error"><?php echo $errors['response']; ?>&nbsp;</td></tr>
            <?php
            }?>
            <tr>
                <td width="120" style="vertical-align:top">
                    <label><strong>Respuesta:</strong></label>
                </td>
                <td>
                    <?php
                    if(($cannedResponses=Canned::responsesByDeptId($ticket->getDeptId()))) {?>
                        <select id="cannedResp" name="cannedResp">
                            <option value="0" selected="selected">Select a canned response</option>
                            <?php
                            foreach($cannedResponses as $id =>$title) {
                                echo sprintf('<option value="%d">%s</option>',$id,$title);
                            }
                            ?>
                        </select>
                        &nbsp;&nbsp;&nbsp;
                        <label><input type='checkbox' value='1' name="append" id="append" checked="checked"> Append</label>
                        <br>
                    <?php
                    }
                    $signature = '';
                    switch ($thisstaff->getDefaultSignatureType()) {
                    case 'dept':
                        if ($dept && $dept->canAppendSignature())
                           $signature = $dept->getSignature();
                       break;
                    case 'mine':
                        $signature = $thisstaff->getSignature();
                        break;
                    } ?>
                    <input type="hidden" name="draft_id" value=""/>
                    <textarea name="response" id="response" cols="50"
                        data-draft-namespace="ticket.response"
                        data-signature-field="signature" data-dept-id="<?php echo $dept->getId(); ?>"
                        data-signature="<?php
                            echo Format::htmlchars(Format::viewableImages($signature)); ?>"
                        placeholder="Escribe tu Respuesta Aqui"
                        data-draft-object-id="<?php echo $ticket->getId(); ?>"
                        rows="9" wrap="soft"
                        class="richtext ifhtml draft"><?php
                        echo $info['response']; ?></textarea>
                </td>
            </tr>
            <?php
            if($cfg->allowAttachments()) { ?>
            <tr>
                <td width="120" style="vertical-align:top">
                    <label for="attachment">Adjuntar:</label>
                </td>
                <td id="reply_form_attachments" class="attachments">
                    <div class="canned_attachments">
                    </div>
                    <div class="uploads">
                    </div>
                    <div class="file_input">
                        <input type="file" class="multifile" name="attachments[]" size="30" value="" />
                    </div>
                </td>
            </tr>
            <?php
            }?>
            <tr>
                <td width="120">
                    <label for="signature" class="left">Firma:</label>
                </td>
                <td>
                    <?php
                    $info['signature']=$info['signature']?$info['signature']:$thisstaff->getDefaultSignatureType();
                    ?>
                    <label><input type="radio" name="signature" value="none" checked="checked"> Ninguna</label>
                    <?php
                    if($thisstaff->getSignature()) {?>
                    <label><input type="radio" name="signature" value="mine"
                        <?php echo ($info['signature']=='mine')?'checked="checked"':''; ?>> Mi Firma</label>
                    <?php
                    } ?>
                    <?php
                    if($dept && $dept->canAppendSignature()) { ?>
                    <label><input type="radio" name="signature" value="dept"
                        <?php echo ($info['signature']=='dept')?'checked="checked"':''; ?>>
                        Firtma del Depto. (<?php echo Format::htmlchars($dept->getName()); ?>)</label>
                    <?php
                    } ?>
                </td>
            </tr>
            <?php
            if($ticket->isClosed() || $thisstaff->canCloseTickets()) { ?>
            <tr>
                <td width="120">
                    <label><strong>Ticket Estatus:</strong></label>
                </td>
                <td>
                    <?php
                    $statusChecked=isset($info['reply_ticket_status'])?'checked="checked"':'';
                    if($ticket->isClosed()) { ?>
                        <label><input type="checkbox" name="reply_ticket_status" id="reply_ticket_status" value="Open"
                            <?php echo $statusChecked; ?>> Reabrir al Responder</label>
                   <?php
                    } elseif($thisstaff->canCloseTickets()) { ?>
                         <label><input type="checkbox" name="reply_ticket_status" id="reply_ticket_status" value="Closed"
                              <?php echo $statusChecked; ?>> Cerrar al Responder</label>
                   <?php
                    } ?>
                </td>
            </tr>
            <?php
            } ?>
         </tbody>
        </table>
        <p  style="padding-left:165px;">
            <input class="btn_sm" type="submit" value="Publicar Respuesta">
            <input class="btn_sm" type="reset" value="Cancelar">
        </p>
    </form>
    <?php
    } ?>
    <form id="note" action="tickets.php?id=<?php echo $ticket->getId(); ?>#note" name="note" method="post" enctype="multipart/form-data">
        <?php csrf_token(); ?>
        <input type="hidden" name="id" value="<?php echo $ticket->getId(); ?>">
        <input type="hidden" name="locktime" value="<?php echo $cfg->getLockTime(); ?>">
        <input type="hidden" name="a" value="postnote">
        <table width="100%" border="0" cellspacing="0" cellpadding="3">
            <?php
            if($errors['postnote']) {?>
            <tr>
                <td width="120">&nbsp;</td>
                <td class="error"><?php echo $errors['postnote']; ?></td>
            </tr>
            <?php
            } ?>
            <tr>
                <td width="120" style="vertical-align:top">
                    <label><strong>Nota Interna:</strong><span class='error'>&nbsp;*</span></label>
                </td>
                <td>
                    <div>
                        <div class="faded" style="padding-left:0.15em">
                        Titulo de la Nota</div>
                        <input type="text" name="title" id="title" size="60" value="<?php echo $info['title']; ?>" >
                        <br/>
                        <span class="error"&nbsp;<?php echo $errors['title']; ?></span>
                    </div>
                    <br/>
                    <textarea name="note" id="internal_note" cols="80"
                        placeholder="Detalles de la Nota"
                        rows="9" wrap="soft" data-draft-namespace="ticket.note"
                        data-draft-object-id="<?php echo $ticket->getId(); ?>"
                        class="richtext ifhtml draft"><?php echo $info['note'];
                        ?></textarea>
                        <span class="error"><?php echo $errors['note']; ?></span>
                        <br>
                </td>
            </tr>
            <?php
            if($cfg->allowAttachments()) { ?>
            <tr>
                <td width="120">
                    <label for="attachment">Adjuntar:</label>
                </td>
                <td class="attachments">
                    <div class="uploads">
                    </div>
                    <div class="file_input">
                        <input type="file" class="multifile" name="attachments[]" size="30" value="" />
                    </div>
                </td>
            </tr>
            <?php
            }
            ?>
            <tr><td colspan="2">&nbsp;</td></tr>
            <tr>
                <td width="120">
                    <label>Ticket Estatus:</label>
                </td>
                <td>
                    <div class="faded"></div>
                    <select name="state">
                        <option value="" selected="selected">&mdash; Sin Cambios &mdash;</option>
                        <?php
                        $state = $info['state'];
                        if($ticket->isClosed()){
                            echo sprintf('<option value="open" %s>Reabrir Ticket</option>',
                                    ($state=='reopen')?'selected="selelected"':'');
                        } else {
                            if($thisstaff->canCloseTickets())
                                echo sprintf('<option value="closed" %s>Cerrar Ticket</option>',
                                    ($state=='closed')?'selected="selelected"':'');

                            /* Ticket open - states */
                            echo '<option value="" disabled="disabled">&mdash; Estados del Ticket  &mdash;</option>';

                            //Answer - state
                            if($ticket->isAnswered())
                                echo sprintf('<option value="unanswered" %s>Marcar como no Contestado</option>',
                                    ($state=='unanswered')?'selected="selelected"':'');
                            else
                                echo sprintf('<option value="answered" %s>Marcar como Contestado</option>',
                                    ($state=='answered')?'selected="selelected"':'');

                            //overdue - state
                            // Only department manager can set/clear overdue flag directly.
                            // Staff with edit perm. can still set overdue date & change SLA.
                            if($dept && $dept->isManager($thisstaff)) {
                                if(!$ticket->isOverdue())
                                    echo sprintf('<option value="overdue" %s>Marcar como Retrasado</option>',
                                        ($state=='answered')?'selected="selelected"':'');
                                else
                                    echo sprintf('<option value="notdue" %s>Descmarcar como Retrasado</option>',
                                        ($state=='notdue')?'selected="selelected"':'');

                                if($ticket->isAssigned())
                                    echo sprintf('<option value="unassigned" %s>Desasignar Ticket</option>',
                                        ($state=='unassigned')?'selected="selelected"':'');
                            }
                        }?>
                    </select>
                    &nbsp;<span class='error'>*&nbsp;<?php echo $errors['state']; ?></span>
                </td>
            </tr>
            </div>
        </table>

       <p  style="padding-left:165px;">
           <input class="btn_sm" type="submit" value="Publicar Nota">
           <input class="btn_sm" type="reset" value="Cancelar">
       </p>
   </form>
    <?php
    if($thisstaff->canTransferTickets()) { ?>
    <form id="transfer" action="tickets.php?id=<?php echo $ticket->getId(); ?>#transfer" name="transfer" method="post" enctype="multipart/form-data">
        <?php csrf_token(); ?>
        <input type="hidden" name="ticket_id" value="<?php echo $ticket->getId(); ?>">
        <input type="hidden" name="a" value="transfer">
        <table width="100%" border="0" cellspacing="0" cellpadding="3">
            <?php
            if($errors['transfer']) {
                ?>
            <tr>
                <td width="120">&nbsp;</td>
                <td class="error"><?php echo $errors['transfer']; ?></td>
            </tr>
            <?php
            } ?>
            <tr>
                <td width="120">
                    <label for="deptId"><strong>Departamento:</strong></label>
                </td>
                <td>
                    <?php
                        echo sprintf('<span class="faded">El Ticket Pertence al Depto. <b>%s</b>.</span>', $ticket->getDeptName());
                    ?>
                    <br>
                    <select id="deptId" name="deptId">
                        <option value="0" selected="selected">&mdash; Seleccionar Departamento &mdash;</option>
                        <?php
                        if($depts=Dept::getDepartments()) {
                            foreach($depts as $id =>$name) {
                                if($id==$ticket->getDeptId()) continue;
                                echo sprintf('<option value="%d" %s>%s</option>',
                                        $id, ($info['deptId']==$id)?'selected="selected"':'',$name);
                            }
                        }
                        ?>
                    </select>&nbsp;<span class='error'>*&nbsp;<?php echo $errors['deptId']; ?></span>
                </td>
            </tr>
            <tr>
                <td width="120" style="vertical-align:top">
                    <label><strong>Comentarios:</strong><span class='error'>&nbsp;*</span></label>
                </td>
                <td>
                    <textarea name="transfer_comments" id="transfer_comments"
                        placeholder="Razon de la Transferencia"
                        class="richtext ifhtml no-bar" cols="80" rows="7" wrap="soft"><?php
                        echo $info['transfer_comments']; ?></textarea>
                    <span class="error"><?php echo $errors['transfer_comments']; ?></span>
                </td>
            </tr>
        </table>
        <p style="padding-left:165px;">
           <input class="btn_sm" type="submit" value="Transferir">
           <input class="btn_sm" type="reset" value="Cancelar">
        </p>
    </form>
    <?php
    } ?>
    <?php
    if($thisstaff->canAssignTickets()) { ?>
    <form id="assign" action="tickets.php?id=<?php echo $ticket->getId(); ?>#assign" name="assign" method="post" enctype="multipart/form-data">
        <?php csrf_token(); ?>
        <input type="hidden" name="id" value="<?php echo $ticket->getId(); ?>">
        <input type="hidden" name="a" value="assign">
        <table style="width:100%" border="0" cellspacing="0" cellpadding="3">

            <?php
            if($errors['assign']) {
                ?>
            <tr>
                <td width="120">&nbsp;</td>
                <td class="error"><?php echo $errors['assign']; ?></td>
            </tr>
            <?php
            } ?>
            <tr>
                <td width="120" style="vertical-align:top">
                    <label for="assignId"><strong>Asignar:</strong></label>
                </td>
                <td>
                    <select id="assignId" name="assignId">
                        <option value="0" selected="selected">&mdash; Seleccionar a un Miembro del Equipo &mdash;</option>
                        <?php
                        if($ticket->isOpen() && !$ticket->isAssigned())
                            echo sprintf('<option value="%d">Reclamar Ticket</option>', $thisstaff->getId());

                        $sid=$tid=0;

                        if ($dept->assignMembersOnly())
                            $users = $dept->getAvailableMembers();
                        else
                            $users = Staff::getAvailableStaffMembers();

                        if ($users) {
                            echo '<OPTGROUP label="Miembros del Equipo ('.count($users).')">';
                            $staffId=$ticket->isAssigned()?$ticket->getStaffId():0;
                            foreach($users as $id => $name) {
                                if($staffId && $staffId==$id)
                                    continue;

                                if (!is_object($name))
                                    $name = new PersonsName($name);

                                $k="s$id";
                                echo sprintf('<option value="%s" %s>%s</option>',
                                        $k,(($info['assignId']==$k)?'selected="selected"':''), $name);
                            }
                            echo '</OPTGROUP>';
                        }

                        if(($teams=Team::getActiveTeams())) {
                            echo '<OPTGROUP label="Equipos ('.count($teams).')">';
                            $teamId=(!$sid && $ticket->isAssigned())?$ticket->getTeamId():0;
                            foreach($teams as $id => $name) {
                                if($teamId && $teamId==$id)
                                    continue;

                                $k="t$id";
                                echo sprintf('<option value="%s" %s>%s</option>',
                                        $k,(($info['assignId']==$k)?'selected="selected"':''),$name);
                            }
                            echo '</OPTGROUP>';
                        }
                        ?>
                    </select>&nbsp;<span class='error'>*&nbsp;<?php echo $errors['assignId']; ?></span>
                    <?php
                    if($ticket->isAssigned() && $ticket->isOpen()) {
                        echo sprintf('<div class="faded">El Ticket esta Asignado a <b>%s</b></div>',
                                $ticket->getAssignee());
                    } elseif ($ticket->isClosed()) { ?>
                        <div class="faded">Asignar responsable a un ticket cerrardo lo <b>Reabrira</b> it!</div>
                    <?php } ?>
                </td>
            </tr>
            <tr>
                <td width="120" style="vertical-align:top">
                    <label><strong>Comments:</strong><span class='error'>&nbsp;*</span></label>
                </td>
                <td>
                    <textarea name="assign_comments" id="assign_comments"
                        cols="80" rows="7" wrap="soft"
                        placeholder="Razones o instrucciones de la asignacion"
                        class="richtext ifhtml no-bar"><?php echo $info['assign_comments']; ?></textarea>
                    <span class="error"><?php echo $errors['assign_comments']; ?></span><br>
                </td>
            </tr>
        </table>
        <p  style="padding-left:165px;">
            <input class="btn_sm" type="submit" value="<?php echo $ticket->isAssigned()?'Reasignar':'Asignar'; ?>">
            <input class="btn_sm" type="reset" value="Cancelar">
        </p>
    </form>
    <?php
    } ?>
</div>
<div style="display:none;" class="dialog" id="print-options">
    <h3>Ticket Print Options</h3>
    <a class="close" href=""><i class="icon-remove-circle"></i></a>
    <hr/>
    <form action="tickets.php?id=<?php echo $ticket->getId(); ?>" method="post" id="print-form" name="print-form">
        <?php csrf_token(); ?>
        <input type="hidden" name="a" value="print">
        <input type="hidden" name="id" value="<?php echo $ticket->getId(); ?>">
        <fieldset class="notes">
            <label for="notes">Print Notes:</label>
            <input type="checkbox" id="notes" name="notes" value="1"> Print <b>Internal</b> Notes/Comments
        </fieldset>
        <fieldset>
            <label for="psize">Paper Size:</label>
            <select id="psize" name="psize">
                <option value="">&mdash; Select Print Paper Size &mdash;</option>
                <?php
                  $options=array('Letter', 'Legal', 'A4', 'A3');
                  $psize =$_SESSION['PAPER_SIZE']?$_SESSION['PAPER_SIZE']:$thisstaff->getDefaultPaperSize();
                  foreach($options as $v) {
                      echo sprintf('<option value="%s" %s>%s</option>',
                                $v,($psize==$v)?'selected="selected"':'', $v);
                  }
                ?>
            </select>
        </fieldset>
        <hr style="margin-top:3em"/>
        <p class="full-width">
            <span class="buttons" style="float:left">
                <input type="reset" value="Reset">
                <input type="button" value="Cancel" class="close">
            </span>
            <span class="buttons" style="float:right">
                <input type="submit" value="Print">
            </span>
         </p>
    </form>
    <div class="clear"></div>
</div>
<div style="display:none;" class="dialog" id="ticket-status">
    <h3><?php echo sprintf('%s Ticket #%s', ($ticket->isClosed()?'Reopen':'Close'), $ticket->getNumber()); ?></h3>
    <a class="close" href=""><i class="icon-remove-circle"></i></a>
    <hr/>
    <?php echo sprintf('Are you sure you want to <b>%s</b> this ticket?', $ticket->isClosed()?'REOPEN':'CLOSE'); ?>
    <form action="tickets.php?id=<?php echo $ticket->getId(); ?>" method="post" id="status-form" name="status-form">
        <?php csrf_token(); ?>
        <input type="hidden" name="id" value="<?php echo $ticket->getId(); ?>">
        <input type="hidden" name="a" value="process">
        <input type="hidden" name="do" value="<?php echo $ticket->isClosed()?'reopen':'close'; ?>">
        <fieldset>
            <div style="margin-bottom:0.5em">
            <em>Reasons for status change (internal note). Optional but highly recommended.</em>
            </div>
            <textarea name="ticket_status_notes" id="ticket_status_notes" cols="50" rows="5" wrap="soft"
                style="width:100%"
                class="richtext ifhtml no-bar"><?php echo $info['ticket_status_notes']; ?></textarea>
        </fieldset>
        <hr style="margin-top:1em"/>
        <p class="full-width">
            <span class="buttons" style="float:left">
                <input type="reset" value="Reset">
                <input type="button" value="Cancel" class="close">
            </span>
            <span class="buttons" style="float:right">
                <input type="submit" value="<?php echo $ticket->isClosed()?'Reopen':'Close'; ?>">
            </span>
         </p>
    </form>
    <div class="clear"></div>
</div>
<div style="display:none;" class="dialog" id="confirm-action">
    <h3>Please Confirm</h3>
    <a class="close" href=""><i class="icon-remove-circle"></i></a>
    <hr/>
    <p class="confirm-action" style="display:none;" id="claim-confirm">
        Are you sure want to <b>claim</b> (self assign) this ticket?
    </p>
    <p class="confirm-action" style="display:none;" id="answered-confirm">
        Are you sure want to flag the ticket as <b>answered</b>?
    </p>
    <p class="confirm-action" style="display:none;" id="unanswered-confirm">
        Are you sure want to flag the ticket as <b>unanswered</b>?
    </p>
    <p class="confirm-action" style="display:none;" id="overdue-confirm">
        Are you sure want to flag the ticket as <font color="red"><b>overdue</b></font>?
    </p>
    <p class="confirm-action" style="display:none;" id="banemail-confirm">
        Are you sure want to <b>ban</b> <?php echo $ticket->getEmail(); ?>? <br><br>
        New tickets from the email address will be auto-rejected.
    </p>
    <p class="confirm-action" style="display:none;" id="unbanemail-confirm">
        Are you sure want to <b>remove</b> <?php echo $ticket->getEmail(); ?> from ban list?
    </p>
    <p class="confirm-action" style="display:none;" id="release-confirm">
        Are you sure want to <b>unassign</b> ticket from <b><?php echo $ticket->getAssigned(); ?></b>?
    </p>
    <p class="confirm-action" style="display:none;" id="changeuser-confirm">
        <span id="msg_warning" style="display:block;vertical-align:top">
        <b><?php echo Format::htmlchars($ticket->getName()); ?></b> &lt;<?php echo $ticket->getEmail(); ?>&gt;
        <br> will no longer have access to the ticket.
        </span>
        Are you sure want to <b>change</b> ticket owner to <b><span id="newuser">this guy</span></b>?
    </p>
    <p class="confirm-action" style="display:none;" id="delete-confirm">
        <font color="red"><strong>Are you sure you want to DELETE this ticket?</strong></font>
        <br><br>Deleted tickets CANNOT be recovered, including any associated attachments.
    </p>
    <div>Please confirm to continue.</div>
    <form action="tickets.php?id=<?php echo $ticket->getId(); ?>" method="post" id="confirm-form" name="confirm-form">
        <?php csrf_token(); ?>
        <input type="hidden" name="id" value="<?php echo $ticket->getId(); ?>">
        <input type="hidden" name="a" value="process">
        <input type="hidden" name="do" id="action" value="">
        <hr style="margin-top:1em"/>
        <p class="full-width">
            <span class="buttons" style="float:left">
                <input type="button" value="Cancel" class="close">
            </span>
            <span class="buttons" style="float:right">
                <input type="submit" value="OK">
            </span>
         </p>
    </form>
    <div class="clear"></div>
</div>
<script type="text/javascript" src="js/ticket.js?4b0877c"></script>
<script type="text/javascript">
$(function() {
    $(document).on('click', 'a.change-user', function(e) {
        e.preventDefault();
        var tid = <?php echo $ticket->getOwnerId(); ?>;
        var cid = <?php echo $ticket->getOwnerId(); ?>;
        var url = 'ajax.php/'+$(this).attr('href').substr(1);
        $.userLookup(url, function(user) {
            if(cid!=user.id
                    && $('.dialog#confirm-action #changeuser-confirm').length) {
                $('#newuser').html(user.name +' &lt;'+user.email+'&gt;');
                $('.dialog#confirm-action #action').val('changeuser');
                $('#confirm-form').append('<input type=hidden name=user_id value='+user.id+' />');
                $('#overlay').show();
                $('.dialog#confirm-action .confirm-action').hide();
                $('.dialog#confirm-action p#changeuser-confirm')
                .show()
                .parent('div').show().trigger('click');
            }
        });
    });
});
</script>
