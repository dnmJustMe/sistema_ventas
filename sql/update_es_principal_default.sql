-- Inicializar es_principal para imágenes existentes: poner 1 a la más reciente de cada producto
UPDATE imagenes AS i
JOIN (
  SELECT producto_id, MAX(id) AS id_max
  FROM imagenes
  GROUP BY producto_id
) t ON t.id_max = i.id
SET i.es_principal = 1
WHERE i.es_principal = 0;