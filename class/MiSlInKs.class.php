<?php

/**
 * MiSlInKs
 * Copyright (C) 2008-2011 Esteban De La Fuente Rubio (esteban@delaf.cl)
 *
 * Este programa es software libre: usted puede redistribuirlo y/o modificarlo
 * bajo los términos de la Licencia Pública General GNU publicada
 * por la Fundación para el Software Libre, ya sea la versión 3
 * de la Licencia, o (a su elección) cualquier versión posterior de la misma.
 *
 * Este programa se distribuye con la esperanza de que sea útil, pero
 * SIN GARANTÍA ALGUNA; ni siquiera la garantía implícita
 * MERCANTIL o de APTITUD PARA UN PROPÓSITO DETERMINADO.
 * Consulte los detalles de la Licencia Pública General GNU para obtener
 * una información más detallada.
 *
 * Debería haber recibido una copia de la Licencia Pública General GNU
 * junto a este programa.
 * En caso contrario, consulte <http://www.gnu.org/licenses/gpl.html>.
 *
 */

/**
 * Administrador de enlaces
 * @author DeLaF, esteban[at]delaf.cl
 * @version 2011-03-02
 */
class MiSlInKs {

	/**
	 * Buscar las categorias de enlaces disponibles
	 * @return Array Arreglo con las categorias disponibles
	 * @author DeLaF, esteban[at]delaf.cl
	 * @version 2011-03-02
	 */
	private static function buscar () {
		$categorias = array();
		if ($gestor = opendir(MISLINKS_DIR.'/enlaces')) { // abrir directorio
			while (($archivo = readdir($gestor)) != false) { // leer directorio
				if($archivo[0]!='.') { // no considerar .*
					array_push($categorias, substr($archivo, 0, -4)); // guardar nombre del archivo (sin extension)
				}
			}
			closedir($gestor); // cerrar gestor
		}
		unset($gestor, $archivo);
		sort($categorias); // ordenar resultado alfabéticamente
		return $categorias;
	}

	/**
	 * Lee el archivo de los enlaces, según categoría, y los carga en un arreglo, devolviendo el mismo
	 * @param archivoCategoria archivo de noticia que se deberá leer (con o sin extensión .txt)
	 * @return Array Arreglo con indices: date, title, author, intro, body, pubDate y link
	 * @author DeLaF, esteban[at]delaf.cl
	 * @version 2011-02-28
	 */
	private static function leer ($archivoCategoria) {
		$lineas = explode("\n", file_get_contents(MISLINKS_DIR.'/enlaces/'.$archivoCategoria.'.txt'));
		$nlineas = count($lineas);
		for($i=0; $i<$nlineas; ++$i) {
			if(empty($lineas[$i])) continue;
			list($enlaces[$i]['name'], $enlaces[$i]['link']) = explode('|', $lineas[$i]);
		}
		return $enlaces;
	}

	/**
	 * Muestra las categorias de enlaces (opcionalmente con sus enlaces) o bien una sola categoría con sus enlaces
	 * @author DeLaF, esteban[at]delaf.cl
	 * @version 2011-03-02
	 */
	public static function mostrar () {
		$categoria = !empty($_GET['categoria']) ? urldecode($_GET['categoria']) : null;
		if($categoria) self::mostrarCategoria($categoria);
		else self::mostrarCategorias();
	}

	/**
	 * Muestra las categorias de enlaces (opcionalmente con sus enlaces)
	 * @author DeLaF, esteban[at]delaf.cl
	 * @version 2011-03-02
	 */
	private static function mostrarCategorias() {
		$categoriasHTML = '';
		$categorias = self::buscar();
		if(MISLINKS_SHOW_ALL) {
			foreach($categorias as &$categoria) {
				$enlacesHTML = '';
				$enlaces = self::leer($categoria);
				foreach($enlaces as &$enlace) {
					$enlacesHTML .= self::generar('enlacesItem.html', $enlace);
				}
				unset($enlaces);
				$categoriasHTML .= self::generar('categoriasItem.html', array('categoria'=>$categoria, 'enlaces'=>$enlacesHTML));
			}
			echo self::generar('categorias.html', array('titulo'=>MISLINKS_TITLE, 'categorias'=>$categoriasHTML), TAB);
		} else {
			foreach($categorias as &$categoria) {
				$categoriasHTML .= self::generar('enlaceCategoria.html', array('categoria'=>$categoria, 'categoria_url'=>urlencode($categoria)));
			}
			echo self::generar('categoriasSolas.html', array('titulo'=>MISLINKS_TITLE, 'categorias'=>$categoriasHTML), TAB);
		}
	}

	/**
	 * Muestra sola categoría con sus enlaces
	 * @param categoria Categoría que se desea mostrar
	 * @author DeLaF, esteban[at]delaf.cl
	 * @version 2011-03-02
	 */
	private static function mostrarCategoria($categoria) {
		$enlacesHTML = '';
		$enlaces = self::leer($categoria);
		foreach($enlaces as &$enlace) {
			$enlacesHTML .= self::generar('enlacesItem.html', $enlace);
		}
		unset($enlaces);
		echo self::generar('categoria.html', array('categoria'=>$categoria, 'enlaces'=>$enlacesHTML));
	}

	/**
	 * Esta método permite utilizar plantillas html en la aplicacion, estas deberán
	 * estar ubicadas en la carpeta template del directorio raiz (de la app)
	 * @param nombrePlantila Nombre del archivo html que se utilizara como plantilla
	 * @param variables Arreglo con las variables a reemplazar en la plantilla
	 * @param tab Si es que se deberán añadir tabuladores al inicio de cada linea de la plantilla
	 * @return String Plantilla ya formateada con las variables correspondientes
	 * @author DeLaF, esteban[at]delaf.cl
	 * @version 2011-03-02
	 */
	public static function generar ($nombrePlantilla, $variables = null, $tab = 0) {

		// definir donde se encuentra la plantilla
		$archivoPlantilla = MISLINKS_DIR.'/template/'.MISLINKS_TEMPLATE.'/'.$nombrePlantilla;

		// cargar plantilla
		$plantilla = file_get_contents($archivoPlantilla);

		// añadir tabuladores delante de cada linea
		if($tab) {
			$lineas = explode("\n", $plantilla);
			foreach($lineas as &$linea) {
				if(!empty($linea)) $linea = constant('TAB'.$tab).$linea;
			}
			$plantilla = implode("\n", $lineas);
			unset($lineas, $linea);
		}

		// reemplazar variables en la plantilla
		if($variables) {
			foreach($variables as $key => $valor)
				$plantilla = str_replace('{'.$key.'}', $valor, $plantilla);
		}

		// retornar plantilla ya procesada
		return $plantilla;

        }

}

?>
