-- 006_sri_config_telefono.sql
-- Telefono del emisor para mostrarlo en la previsualizacion y RIDE de factura.

SET @db := DATABASE();

SET @sql := IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'facturas_electronicas_config' AND COLUMN_NAME = 'telefono') = 0,
    'ALTER TABLE facturas_electronicas_config ADD COLUMN telefono VARCHAR(50) NOT NULL DEFAULT '''' AFTER direccion_establecimiento',
    'SELECT ''telefono ya existe'' AS status'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
