<?php
$GLOBALS['TYPO3_CONF_VARS']['DB']['host'] = getenv('DB_HOST');
$GLOBALS['TYPO3_CONF_VARS']['DB']['username'] = getenv('DB_USER');
$GLOBALS['TYPO3_CONF_VARS']['DB']['password'] = getenv('DB_PASS');
$GLOBALS['TYPO3_CONF_VARS']['DB']['port'] = getenv('DB_PORT');
$GLOBALS['TYPO3_CONF_VARS']['DB']['database'] = getenv('DB_NAME');
$GLOBALS['TYPO3_CONF_VARS']['BE']['installToolPassword'] = md5(getenv('INSTALL_TOOL_PASSWORD'));
$GLOBALS['TYPO3_CONF_VARS']['SYS']['displayErrors'] = 1;
$GLOBALS['TYPO3_CONF_VARS']['SYS']['systemLog'] = 'error_log';
$GLOBALS['TYPO3_CONF_VARS']['SYS']['systemLogLevel'] = '0';
$GLOBALS['TYPO3_CONF_VARS']['SYS']['enable_errorDLOG'] = '1';
$GLOBALS['TYPO3_CONF_VARS']['SYS']['enable_exceptionDLOG'] = '1';
$GLOBALS['TYPO3_CONF_VARS']['SYS']['enableDeprecationLog'] = 'console';
