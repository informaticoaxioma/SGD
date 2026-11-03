# Documentación Técnica: Funcionamiento del MCP / Chat IA del SGD

Este documento detalla la arquitectura, inicialización, flujo de datos, seguridad, funcionalidades actuales y la guía paso a paso para extender el módulo de Inteligencia Artificial (**MCP / Chat IA**) del **Sistema de Gestión Documental (SGD)** de Axioma.

---

## 1. Archivos que Componen el Módulo

El módulo trabaja sobre **5 archivos principales** distribuidos entre la navegación, la vista, el motor de procesamiento (MCP) y la capa de configuración/datos:

| Archivo | Ruta en el Proyecto | Responsabilidad Principal |
| :--- | :--- | :--- |
| `navbarHorizontal.php` | `public_html/pa/navBar/navbarHorizontal.php` (línea 123) | Agrega el botón **"Chat con IA"** en el menú lateral del SGD y dispara `cargarChatIA()`. |
| `llamadasAjax.js` | `public_html/pa/funcionesJS/llamadasAjax.js` (líneas 309-314) | Define `cargarChatIA()`, que inyecta la vista `chatIA/chatIA.php` dentro del contenedor `#page-wrapper` de `pa/inicio.php`. |
| `chatIA.php` | `public_html/pa/chatIA/chatIA.php` | Renderiza el panel Bootstrap 3 con diseño Axioma, barra de sesión/contrato, chips de consulta rápida, historial de mensajes y funciones JS (`sendMessage()`, `formatMarkdown()`). |
| `procesarChatIA.php` | `public_html/pa/chatIA/procesarChatIA.php` | Motor MCP / Backend IA: recibe la consulta, valida sesión y contrato en BD, clasifica la intención con Gemini (Paso 1), ejecuta las consultas parametrizadas o conteos históricos en MySQL (Paso 2) y genera la respuesta final con Gemini (Paso 3). |
| `config_ai.php` y `Conexion.php` | `public_html/data/config_ai.php` / `public_html/data/Conexion.php` | Define `GEMINI_API_KEY` / `GEMINI_MODEL` y gestiona la conexión MySQL hacia `gestor_documental` (`10.50.0.11:3306`). |

---

## 2. ¿Dónde se Inicializa y Cómo se Llama?

1. **Menú de Navegación (`pa/navBar/navbarHorizontal.php`)**:
   Cuando el usuario hace clic en `<a href="#chatIA" onclick="cargarChatIA()">`, se invoca la función `cargarChatIA()` en `pa/funcionesJS/llamadasAjax.js`.
2. **Carga de la Vista (`pa/chatIA/chatIA.php`)**:
   - Inicia o retoma la sesión PHP (`session_start()`) y verifica `$_SESSION['usuario']`.
   - Obtiene el `id_contrato` del usuario logueado y consulta el nombre del contrato mediante `Contrato::getContratoPorId()`.
   - Al cargarse en el navegador, verifica si existe una API Key guardada en `localStorage` (`sgd_gemini_api_key`) o definida globalmente en `data/config_ai.php`.
3. **Llamada al Backend (`sendMessage()` en `pa/chatIA/chatIA.php`)**:
   - Realiza una petición `$.ajax` tipo `POST` a `chatIA/procesarChatIA.php` enviando en formato JSON:
     ```json
     {
       "query": "dame el último documento que tiene como responsable el Jefe Asesoria",
       "api_key": "AIzaSy..."
     }
     ```

---

## 3. ¿Cómo Procesa los Datos? (Arquitectura en 3 Etapas)

El motor se encuentra en `public_html/pa/chatIA/procesarChatIA.php` y opera bajo una arquitectura **RAG (Retrieval-Augmented Generation)** dividida en **Validación Preliminar + 3 Pasos**:

### Etapa Preliminar: Seguridad y Contexto del Contrato
- El `id_contrato` se toma exclusivamente de la sesión del servidor (`$_SESSION['usuario']`) y se revalida contra la tabla `usuario` en la base de datos (`u.id_estado_usuario = 1`).
- Precarga la lista de todos los usuarios/responsables activos de ese contrato (`$usuariosContrato` y `$nombresResponsablesContrato`) para que la IA reconozca que cargos como *"Jefe Asesoría"*, *"Encargada de Gestión"* o *"Asesor Legal"* son **responsables internos del contrato**.

### Paso 1: Clasificación de Intención y Extracción de Variables con IA
Se invoca `llamarGemini($apiKey, $stage1Prompt, true)` con `responseMimeType: "application/json"`.
Gemini analiza la frase en lenguaje natural (corrigiendo errores de tipeo o falta de tildes) y devuelve un JSON estructurado con:
- **`intent`**: `'document'`, `'user'`, `'contract'` o `'general'`.
- **`keywords`**: Palabras clave específicas (filtradas luego con `$stopwords`).
- **`filters`**:
  - `responsable`, `remitente`, `destinatario`
  - `tipo_documento`, `materia`, `subcontrato`, `doc_nombre`, `doc_numero`
  - `estado_doc` (`1` = Pendiente, `2` = Cerrado), `flujo_doc` (`1` = Entrada, `2` = Salida)
  - `solo_mis_documentos` (booleano)
  - `es_consulta_cantidad` (booleano: activa el modo de conteo histórico)
  - `clasificar_por` (`'mes'`, `'anio'`, `'tipo_documento'`, `'estado'`, `'flujo'`, `'responsable'`, `'subcontrato'`)
  - `anio` (año de 4 dígitos, ej. `2026`) y `mes` (`1`..`12` cuando se filtra un único mes puntual)
  - `orden_recientes` (booleano)
  - `fecha_desde`, `fecha_hasta`

### Paso 2: Ejecución de Consultas SQL Seguras en MySQL
Con las variables extraídas en el Paso 1, `construirBaseWhereDocumentos()` arma los `JOIN` y la cláusula `WHERE` parametrizada (`mysqli_prepare`), utilizando:
- `sqlLikeUtf8()`: Convierte columnas con distintas colaciones (`latin1_bin` / `latin1_swedish_ci`) a `utf8_general_ci` para hacer búsquedas insensibles a tildes y mayúsculas sin errores de colación.
- `normalizarTexto()` y `extraerPalabrasSignificativas()`: Emparejan nombres y cargos aunque tengan espacios dobles en la base de datos o errores de tildes.

El flujo toma uno de dos caminos excluyentes para documentos:
1. **Modo Cifras y Cantidades (`$esConsultaCantidad === true`)**:
   - Llama a `contarDocumentosHistoricosPorVariables()`.
   - Cuenta con `COUNT(DISTINCT d.id_documento)` todo el **registro histórico** del contrato (o del filtro indicado) y genera desgloses por estado, flujo, año, tipo de documento, responsable y subcontrato.
   - **No retorna el detalle individual de documentos**.
2. **Modo Detalle / Búsqueda de Documentos (`$esConsultaCantidad === false`)**:
   - Llama a `buscarDocumentosPorVariables()`.
   - Ordena siempre desde el documento más reciente hacia atrás (`2026 -> 2025 -> ...`) usando `GREATEST(fecha_documento, fecha_recepcion) DESC`.
   - Formatea cada fila con `formatearFilaDocumento()`.

### Paso 3: Generación de la Respuesta Final (RAG)
Se construye `$stage2Prompt` inyectando únicamente los datos reales obtenidos en `$dbResults` y se envía a Gemini:
- Si fue una **consulta de cifras/cantidades**, Gemini entrega únicamente los números y desgloses estadísticos históricos sin listar documentos.
- Si fue una **consulta de documentos**, Gemini presenta las variables del documento e incluye el enlace directo de descarga: `[Descargar Documento](negocio/descargarDocumento.php?idDocumento=ID)`.

---

## 4. ¿Cómo Agregar Nuevas Funcionalidades?

Para añadir una nueva capacidad al MCP (por ejemplo, **consultar Hitos Contractuales** o **consultar la Bitácora**), sigue estos **4 pasos** en `public_html/pa/chatIA/procesarChatIA.php`:

1. **Paso A (Prompt Paso 1)**: En `$stage1Prompt`, declara el nuevo `intent` o la nueva propiedad dentro de `filters`.
2. **Paso B (Función SQL)**: Crea la función PHP en `procesarChatIA.php` usando `ejecutarSelect($cnx, $sql, $params, $types)` y filtrando siempre por `$userContractId`.
3. **Paso C (Ejecución Paso 2)**: Llama a tu nueva función y guarda su resultado dentro del arreglo `$dbResults['nueva_clave']`.
4. **Paso D (Prompt Paso 3)**: En `$stage2Prompt`, agrega una regla indicando cómo debe formatear o explicar los datos de `nueva_clave`.

