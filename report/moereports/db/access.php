<?php

$capabilities = array(
    'report/moereport:viewall' => array(
        'riskbitmask' => RISK_PERSONAL,
        'captype' => 'read',
        'contextlevel' => CONTEXT_USER,
        'legacy' => array(
            'manager' => CAP_ALLOW
        )
     )
);
