-- Agregar columna es_principal a tabla imagenes
ALTER TABLE `imagenes`
  ADD COLUMN `es_principal` TINYINT(1) NOT NULL DEFAULT 0 AFTER `path`;

-- Asegurar que haya solo una principal por producto (opcional, chequeo a nivel app)
-- Para forzar unicidad a nivel BD, se podría usar un índice parcial en MySQL 8+ con expresión o un trigger.
-- Aquí dejamos control de unicidad al backend.
