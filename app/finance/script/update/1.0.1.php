<?php
echo PHP_EOL.' uninstall accounting...';
//卸载app accounting  accountinganalysis
$shell->exec_command("uninstall accounting");
$shell->exec_command("uninstall accountinganalysis");