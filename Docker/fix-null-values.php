<?php
if ($container->getParameter('brievenhulp.mailer_encryption') === 'null') {
    $container->setParameter('brievenhulp.mailer_encryption', null);
}
if ($container->getParameter('brievenhulp.piwik_site_id') === 'null') {
    $container->setParameter('brievenhulp.piwik_site_id', null);
}
