<?php
if (!isset($e) or (!$e instanceOf \Throwable)) {
  return 'Error: No Exception or Throwable instance given';
  return;
}
ob_start();
?>
<p>Exception class <?php echo get_class($e); ?></b></p>
<h2 style="border-bottom: 1px solid black"><?php echo $e->getMessage(); ?></h2>

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

$t = $trace[$i];
?>

<tr>
  <td style="text-align: left; vertical-align: top;"><?php echo ($trace_count - $i); ?></td>
  <td style="text-align: left; vertical-align: top;"><?php echo isset($t['file']) ? $t['file'] : ""; ?></td>
  <td style="text-align: left; vertical-align: top;"><?php echo isset($t['line']) ? $t['line'] : ""; ?></td>
  <td style="text-align: left; vertical-align: top;"><?php echo isset($t['function']) ? $t['function'] : ""; ?></td>
  <td style="text-align: left; vertical-align: top;"><?php echo (!empty($t['args'])) ? json_encode( $t['args'] ) : ""; ?></td>
</tr>

<?php
endfor;
?>
</table>

<?php if (!empty($package)): ?>
<hr>
<h5>About <?php echo $package['title'];?></h5>
<p>This exception report was created by <b><?php echo $package['middleware'];?></b>
which is part of <a href="<?php echo $package['packagist'];?>"><?php echo $package['name'];?></a></p>

<?php
endif;
return ob_get_clean();
