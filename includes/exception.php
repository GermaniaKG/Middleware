<?php
if (!isset($e) or (!$e instanceOf \Exception)) {
  return 'Error: No Exception instance given';
  return;
}
ob_start();
?>
<p>Exception class <?php echo get_class($e); ?></b></p>
<h2 style="border-bottom: 1px solid black"><?php echo $e.getMessage(); ?></h2>

<table>

<tr><th style="text-align: left">Code</th><td><?php echo $e->getCode(); ?></td></tr>
<tr><th style="text-align: left">File</th><td><?php echo $e->getFile(); ?></td></tr>
<tr><th style="text-align: left">Line</th><td><?php echo $e->getLine(); ?></td></tr>

</table>

<h2 style="border-bottom: 1px solid black">Trace</h2>

<table>

<tr>
  <th style="text-align: left">No.</th>
  <th style="text-align: left">File</th>
  <th style="text-align: left">Line</th>
  <th style="text-align: left">Function</th>
  <th style="text-align: left">Args</th>
</tr>

<?php
$trace       = $e->getTrace();
$trace_count = count($trace);
for( $i = 0; $i < $trace_count; $i++):
?>

<tr>
  <td style="text-align: left"><?php echo ($trace_count - $i); ?></td>
  <td style="text-align: left"><?php echo $t['file']; ?></td>
  <td style="text-align: left"><?php echo $t['line']; ?></td>
  <td style="text-align: left"><?php echo $t['function']; ?></td>
  <td style="text-align: left"><?php echo (!empty($t['args'])) ? json_encode( $t['args'] ) : ""; ?></td>
</tr>

<?php
endfor;
?>
</table>

<?php
return ob_get_clean();
