# Manual de Usuario - MI HOGAR EN LINEA

## Instrucciones para Compilar el Manual

Este manual está escrito en LaTeX y requiere un compilador LaTeX para generar el PDF.

### Requisitos

1. **Distribución LaTeX:**
   - Windows: MiKTeX o TeX Live
   - Mac: MacTeX
   - Linux: TeX Live

2. **Editor recomendado:**
   - TeXstudio
   - Overleaf (online)
   - TeXmaker
   - Visual Studio Code con extensión LaTeX

### Compilación

#### Opción 1: Usando TeXstudio
1. Abre `MANUAL_USUARIO.tex` en TeXstudio
2. Haz clic en "Compilar" (F5)
3. El PDF se generará automáticamente

#### Opción 2: Usando línea de comandos
```bash
pdflatex MANUAL_USUARIO.tex
pdflatex MANUAL_USUARIO.tex  # Ejecutar dos veces para referencias cruzadas
```

#### Opción 3: Usando Overleaf (Online)
1. Ve a https://www.overleaf.com
2. Crea un nuevo proyecto
3. Sube el archivo `MANUAL_USUARIO.tex`
4. Sube las imágenes a la carpeta `screenshots/`
5. Compila el documento

### Estructura de Carpetas

```
mi-hogar-en-linea/
├── MANUAL_USUARIO.tex
├── screenshots/
│   ├── 01_registro_formulario.png
│   ├── 02_registro_exitoso.png
│   ├── 03_login_formulario.png
│   ├── 04_dashboard_principal.png
│   ├── 05_pagina_principal.png
│   ├── 06_buscador_principal.png
│   ├── 07_listado_propiedades.png
│   ├── 08_filtros_avanzados.png
│   ├── 09_detalles_propiedad.png
│   ├── 10_contacto_whatsapp.png
│   ├── 11_dashboard_completo.png
│   ├── 12_sidebar_menu.png
│   ├── 13_estadisticas.png
│   ├── 14_formulario_publicar.png
│   ├── 15_subir_imagenes.png
│   ├── 16_publicacion_exitosa.png
│   ├── 17_mis_propiedades.png
│   ├── 18_editar_propiedad.png
│   ├── 19_imagenes_existentes.png
│   ├── 20_confirmar_eliminar.png
│   ├── 21_configuracion.png
│   ├── 22_cambios_guardados.png
│   ├── 23_sobre_nosotros.png
│   └── 24_contacto.png
└── README_MANUAL.md
```

### Agregar Pantallazos

1. Toma capturas de pantalla de cada funcionalidad
2. Guárdalas en la carpeta `screenshots/` con los nombres indicados
3. Asegúrate de que las imágenes sean en formato PNG o JPG
4. Recomendado: Resolución de 1920x1080 o similar para buena calidad

### Personalización

Puedes modificar:
- Colores en la sección de configuración
- Tamaño de imágenes ajustando `width` en `\includegraphics`
- Fuentes y estilos en las secciones de configuración
- Contenido agregando o modificando secciones

### Notas Importantes

- Las imágenes deben estar en la carpeta `screenshots/` relativa al archivo .tex
- Si cambias nombres de imágenes, actualiza las referencias en el documento
- Compila dos veces para que las referencias cruzadas funcionen correctamente
- El documento incluye tabla de contenidos e índice de figuras automáticos

### Solución de Problemas

**Error: "File not found" para imágenes:**
- Verifica que las imágenes estén en la carpeta `screenshots/`
- Verifica que los nombres de archivo coincidan exactamente

**Error de compilación:**
- Asegúrate de tener todos los paquetes instalados
- Ejecuta: `pdflatex MANUAL_USUARIO.tex` dos veces

**Referencias no funcionan:**
- Compila el documento dos veces seguidas
- O usa `makeindex` si es necesario

