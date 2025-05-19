# Integración de Marqo con WordPress y Fibo Search

## Análisis de la API de Marqo

Marqo es un motor de búsqueda unificado que genera embeddings y permite realizar búsquedas vectoriales. Su API ofrece endpoints para búsqueda de texto, imágenes y contenido multimodal. Los principales endpoints relevantes para nuestra integración son:

- **POST /indexes/{index_name}/search**: Permite realizar búsquedas en un índice específico.
- Parámetros principales:
  - `q`: Cadena de consulta o query string
  - `limit`: Número máximo de resultados a devolver
  - `filter`: Filtros para refinar los resultados
  - `searchableAttributes`: Atributos en los que buscar

La API de Marqo requiere autenticación mediante una clave API que debe configurarse en las solicitudes HTTP.

## Análisis de Fibo Search

Fibo Search (anteriormente Ajax Search for WooCommerce) es un plugin de WordPress que proporciona funcionalidades de búsqueda avanzada para tiendas WooCommerce. Características relevantes:

- Mantiene su propio índice de búsqueda independiente
- El índice se construye automáticamente tras la instalación, actualizaciones o cambios en la configuración
- Escucha cambios en productos, atributos y términos a través de hooks como `save_post`, `deleted_post`, `edited_term`, etc.
- Ofrece hooks y filtros para modificar su comportamiento

## Puntos de integración identificados

Para inyectar resultados de Marqo en Fibo Search, hemos identificado los siguientes puntos de integración:

1. **Hooks de resultados de búsqueda**: Fibo Search probablemente utiliza filtros para procesar y mostrar resultados de búsqueda.
2. **Eventos JavaScript**: Fibo Search proporciona eventos JavaScript que podemos utilizar para interceptar consultas y modificar resultados.
3. **Indexación personalizada**: Podemos extender la indexación de Fibo Search para incluir datos de Marqo.

## Arquitectura propuesta

La arquitectura del plugin se basará en los siguientes componentes:

1. **Clase principal del plugin**: Inicializa el plugin y registra hooks y filtros.
2. **Conector de Marqo**: Gestiona la comunicación con la API de Marqo.
3. **Interceptor de búsqueda**: Intercepta las consultas de Fibo Search y las enriquece con resultados de Marqo.
4. **Administrador de configuración**: Proporciona una interfaz para configurar la integración.
5. **Caché de resultados**: Optimiza el rendimiento almacenando resultados frecuentes.

## Flujo de datos

1. El usuario realiza una búsqueda en el sitio de WordPress utilizando Fibo Search.
2. El plugin intercepta la consulta antes de que Fibo Search devuelva los resultados.
3. El plugin envía la misma consulta a Marqo a través de su API.
4. Los resultados de Marqo se procesan y se combinan con los resultados originales de Fibo Search.
5. Los resultados combinados se devuelven al usuario.

## Consideraciones técnicas

- **Rendimiento**: Las llamadas a la API externa pueden afectar el tiempo de respuesta. Implementaremos caché para mitigar este problema.
- **Autenticación**: Necesitaremos almacenar de forma segura las credenciales de la API de Marqo.
- **Compatibilidad**: El plugin debe funcionar con diferentes versiones de WordPress, WooCommerce y Fibo Search.
- **Personalización**: Proporcionaremos opciones para configurar cómo se combinan los resultados (prioridad, proporción, etc.).
