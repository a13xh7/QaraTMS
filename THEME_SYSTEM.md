# QaraTMS Modern Theme System

## 🎨 Características Implementadas

### Sistema de Temas Completo

-   **Tema Automático**: Detecta y sigue las preferencias del sistema operativo
-   **Tema Claro**: Interfaz optimizada para entornos bien iluminados
-   **Tema Oscuro**: Interfaz que reduce la fatiga visual en entornos con poca luz

### Detección Inteligente

-   Detección automática del tema del sistema usando `prefers-color-scheme`
-   Sincronización en tiempo real cuando el usuario cambia el tema del sistema
-   Persistencia de preferencias en localStorage y base de datos

### UI Moderna e Interactiva

-   Transiciones suaves entre temas (0.3s ease-in-out)
-   Efectos hover mejorados en tarjetas y botones
-   Sistema de ondas (ripple effect) en botones
-   Animaciones de carga y feedback visual
-   Tipografía mejorada con fuente Inter

## 🚀 Funcionalidad

### Botón de Cambio de Tema

-   Ubicado en el header de navegación
-   Cicla entre: Auto → Claro → Oscuro → Auto
-   Iconos intuitivos: 🌓 (Auto), ☀️ (Claro), 🌙 (Oscuro)
-   Atajo de teclado: `Ctrl/Cmd + Shift + T`

### Configuración de Usuario

-   Panel en la página de edición de usuario
-   Persistencia en la base de datos (campo `theme_preference`)
-   Sincronización automática entre dispositivos del mismo usuario

### API RESTful

```javascript
// Obtener preferencia actual
GET /api/user/theme-preference

// Actualizar preferencia
POST /api/user/theme-preference
{
    "theme": "auto|light|dark"
}
```

## 🎯 Archivos Principales

### Frontend

-   `public/css/modern-theme.css` - Variables CSS y estilos del tema
-   `public/js/theme-manager.js` - Lógica de cambio de tema
-   `public/js/ui-enhancements.js` - Efectos visuales adicionales

### Backend

-   `app/Http/Controllers/Api/ThemeController.php` - API de preferencias
-   `database/migrations/*_add_theme_preference_to_users_table.php` - Migración
-   `app/Models/User.php` - Campo `theme_preference` agregado

### Views

-   `resources/views/layout/base_layout.blade.php` - CSS/JS incluidos
-   `resources/views/layout/header_nav.blade.php` - Botón de tema
-   `resources/views/layout/sidebar_nav.blade.php` - Sidebar modernizado
-   `resources/views/users/edit_page.blade.php` - Configuración de usuario

### Traducciones

-   `resources/lang/en/ui.php` - Textos en inglés
-   `resources/lang/es/ui.php` - Textos en español

## 🔧 Variables CSS Principales

```css
/* Colores principales */
--primary-color: #3b82f6
--bg-primary: #ffffff (claro) / #0f172a (oscuro)
--text-primary: #1e293b (claro) / #f8fafc (oscuro)
--border-color: #e2e8f0 (claro) / #334155 (oscuro)

/* Transiciones */
--transition-fast: 0.15s ease-in-out
--transition-normal: 0.3s ease-in-out

/* Sombras adaptables */
--shadow-md: Variable según el tema
```

## 📱 Responsividad

-   Breakpoint móvil: 768px
-   Sidebar colapsable en dispositivos móviles
-   Botón de tema adapta texto según el espacio disponible
-   Iconos y controles optimizados para touch

## 🎨 Efectos Visuales

### Animaciones

-   Fade-in del contenido principal (0.5s)
-   Hover effects en tarjetas (translateY -2px)
-   Ripple effect en botones
-   Smooth scroll para navegación interna

### Feedback de Usuario

-   Toast notifications para cambios de tema
-   Estados de validación visual en formularios
-   Loading states en formularios con spinners
-   Hover feedback en todos los elementos interactivos

## 🔐 Seguridad

-   Validación de entrada en API (enum: auto|light|dark)
-   Autenticación requerida para guardar preferencias
-   Protección CSRF en todas las peticiones
-   Fallback seguro a tema 'auto' si hay errores

## 📊 Compatibilidad

### Navegadores Soportados

-   Chrome 76+
-   Firefox 67+
-   Safari 12.1+
-   Edge 79+

### Características Detectadas

-   `prefers-color-scheme` para detección del sistema
-   `localStorage` para persistencia local
-   `CSS Custom Properties` para theming dinámico

## 🚀 Uso Rápido

1. **Cambio Manual**: Click en el botón 🌓 en el header
2. **Atajo de Teclado**: `Ctrl/Cmd + Shift + T`
3. **Configuración Permanente**: Editar perfil de usuario
4. **Automático**: El sistema detecta automáticamente las preferencias del SO

## 🧪 Testing

```bash
# Limpiar caches
php artisan config:clear && php artisan cache:clear

# Probar en Tinker
php artisan tinker
$user = User::first();
$user->theme_preference = 'dark';
$user->save();
```

## 📈 Mejoras Futuras Sugeridas

1. **Temas Personalizados**: Permitir colores custom por usuario
2. **Modo Alto Contraste**: Para accesibilidad
3. **Scheduler de Temas**: Cambio automático según hora del día
4. **Temas por Proyecto**: Diferentes esquemas de color por proyecto
5. **Export/Import**: Configuraciones de tema compartibles

---

**Desarrollado para QaraTMS** - Sistema completo de gestión de temas con detección automática y preferencias de usuario.
