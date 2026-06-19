<?php
	$errorAlertTitulo = $errorAlertTitulo ?? 'Error inesperado';
	$errorAlertTexto = $errorAlertTexto ?? 'No se pudo cargar los datos solicitados.';
?>
<article class="message is-danger">
	<div class="message-header">
		<p><?php echo htmlspecialchars($errorAlertTitulo, ENT_QUOTES, 'UTF-8'); ?></p>
	</div>
	<div class="message-body"><?php echo htmlspecialchars($errorAlertTexto, ENT_QUOTES, 'UTF-8'); ?></div>
</article>
<?php unset($errorAlertTitulo, $errorAlertTexto); ?>
