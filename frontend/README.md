# Frontend E-commerce — Next.js 16 (App Router) + TypeScript + Tailwind

**Realizado por Roberto Lopez** · Práctica FullStack · Participación individual

Construí el frontend de la tienda en Next.js (App Router + TypeScript + Tailwind) para consumir la API REST
de e-commerce que hice antes en Laravel 12 (Sanctum, Swagger y Stripe). Con la app se puede recorrer el flujo
completo: ver el catálogo, registrarse, armar un carrito, pagar con Stripe y consultar el historial de compras.

## El Paso a Paso

Necesito tres cosas encendidas, cada una en su terminal:

| Servicio | Dónde | Comando |
|---|---|---|
| MySQL | Panel de XAMPP | Start |
| API Laravel | `laravel/api-ecommerce-segura` | `php artisan serve --no-reload` (http://127.0.0.1:8000) |
| Frontend | `NEXT/ecommerce-front` | `npm install` y `npm run dev` (http://localhost:3000) |

El `.env.local` (hay un `.env.example` de guía):

```
API_URL=http://127.0.0.1:8000/api
NEXT_PUBLIC_STRIPE_PUBLISHABLE_KEY=pk_test_...   # solo la clave PUBLICABLE
```

La clave publicable (frontend) y la secreta (Laravel) tienen que ser de la misma cuenta de Stripe, en modo prueba.
Si cambio el `.env.local` hay que reiniciar `npm run dev`.
Para probar el pago uso la tarjeta `4242 4242 4242 4242`, cualquier fecha futura y cualquier CVC.

## Cómo lo armé

- **Lecturas en el servidor.** El catálogo y el detalle son Server Components que llaman a la API con `fetch`
  (`src/lib/api.ts`). Cacheo el catálogo con `tags` y `revalidate: 60` para poder invalidarlo después de una compra.
- **Mutaciones con Server Actions.** Login, registro, logout y crear orden están en `src/lib/actions.ts`.
- **Token en cookie `httpOnly`.** Al iniciar sesión guardo el token de Sanctum en una cookie `httpOnly` con
  `sameSite=lax` (y `secure` en producción), así el JavaScript del navegador nunca lo ve.
- **Rutas protegidas.** `src/proxy.ts` (en Next 16 `middleware.ts` pasó a llamarse `proxy.ts`) protege `/orders` y
  `/checkout`. Si la API responde 401 también mando al usuario a `/login`. El parámetro `?next=` solo acepta rutas
  internas para evitar redirecciones abiertas.
- **Carrito local.** Context + `useReducer`, guardado en `localStorage`, con la cantidad limitada al stock.
- **Pago.** `POST /orders` crea la orden y devuelve un `client_secret`; con ese secreto monto el `PaymentElement` de
  Stripe. En `/checkout/success` no me fío del parámetro de la URL: consulto `GET /payments/{order}/status` para
  mostrar el estado real.
- **Datos de usuario sin caché.** `/orders` y `/payments/...` se piden con `cache: "no-store"` para que nunca se
  mezclen datos entre usuarios.

## Requisitos del enunciado

| Requisito | Dónde lo resolví |
|---|---|
| Catálogo y detalle públicos | `/products` (con búsqueda y paginación) y `/products/[id]` |
| Registro y login | `(auth)/login` y `(auth)/register` + Server Actions |
| Token seguro | Cookie `httpOnly` en `lib/actions.ts` |
| Carrito y orden | `cart-provider.tsx` y `createOrderAction` |
| Pago con Stripe y confirmación | `checkout/` y `checkout/success` |
| Historial protegido | `/orders` |
| `loading.tsx` y `error.tsx` | En `products`, `checkout` y `orders` |
| `Suspense` | Catálogo, historial y barra de navegación |
| Evitar datos desactualizados | `revalidateTag` + `revalidatePath` al crear una orden (porque baja el stock) |
| Lighthouse | Sección de abajo y reportes en `docs/` |

## Cambios que hice en el backend

Mientras integraba, revisé la API y le hice estos ajustes:

- **Swagger:** corregí el host (el servidor salía como un texto literal) y documenté los campos de `register`, `login` y `POST /orders` para poder probarlos con "Try it out".
- **Stock:** si Stripe falla al crear el pago, ahora se devuelve el stock y la orden queda como `failed`. Lo comprobé forzando una clave inválida: el stock quedó igual que antes.
- **`per_page`:** `/products` aceptaba cualquier valor; ahora se limita entre 1 y 50.
- **Secretos:** oculté `stripe_client_secret` y `raw_response` en las respuestas del historial. El `client_secret` solo sale al crear la orden.
- **Fuerza bruta:** agregué `throttle` al login (5 intentos por minuto por correo e IP) y al registro. Lo probé hasta ver el 429.
- **Tokens:** ahora vencen a los 7 días, igual que la cookie del frontend.

## Problemas que encontré y cómo los resolví

- **Error 500 al registrar:** el log de Laravel decía que no se podía conectar a la base de datos; MySQL estaba apagado.
- **`artisan serve` no arrancaba en Windows:** la variable `PHP_CLI_SERVER_WORKERS` no funciona ahí; la comenté y uso `--no-reload`.
- **Swagger sin cargar:** un controlador quedó cortado al editarlo y rompía la generación. `php -l` me señaló el archivo.
- **"Expected publishable key to be of type string":** mi `.env.local` usaba el nombre `STRIPE_KEY`; el frontend necesita `NEXT_PUBLIC_STRIPE_PUBLISHABLE_KEY`.
- **"Invalid API Key provided":** la clave publicable estaba mal copiada; la volví a copiar desde el panel de Stripe con el botón de copiar.
- **Mensaje del carrito:** decía "Tienes 2" con un solo producto; era un error mío al contar y lo corregí.

## Rendimiento (Lighthouse)

Medí en modo producción (`npm run build && npm start`), con dispositivo Mobile y en una ventana de incógnito.

| Página | Performance | Accessibility | Best Practices | SEO | LCP | CLS | TBT |
|---|---|---|---|---|---|---|---|
| `/products` | 100 | 100 | 100 | 100 | 1.7 s | 0 | 30 ms |
| `/products/1` | 100 | 100 | 100 | 100 | 1.1 s | 0 | 30 ms |
| `/checkout` | 96 | 100 | 100 | 100 | 2.4 s | 0.001 | 150 ms |

Reportes completos en `docs/`: `lighthouse-products.html`, `lighthouse-product-detail.html` y `lighthouse-checkout.html`.

- `/checkout` baja a 96 porque es la única página que carga el SDK de Stripe (un script de terceros). Aun así todas sus métricas quedan en el rango "bueno".
- El CLS es prácticamente 0 porque cada tarjeta reserva su espacio con `aspect-square` y uso `next/font`.
- Lighthouse avisa que las páginas no entran al bfcache. Es porque las rutas dependen de la cookie de sesión, así que Next.js envía `no-store`. Lo dejé así a propósito: prefiero seguridad sobre un botón "Atrás" más rápido. Ese aviso no resta puntos.
- Mis productos de prueba no tienen imagen (`image_url` es `null`); con fotos reales el LCP dependería de ellas.

## Lo que quedó pendiente

- **Órdenes abandonadas:** si alguien crea una orden y no paga, el stock queda retenido. La solución sería un comando programado en Laravel que cancele las órdenes viejas, pero no llegué a implementarlo.
- **Webhook de Stripe:** no puede llegar a `localhost`, así que el estado "pagado" se sincroniza cuando se consulta `/payments/{order}/status`.
- **Imágenes de producto:** el catálogo de prueba no las tiene, no me alcanzo el tiempo :S

## Uso de herramientas de IA

Usé IA como apoyo para armar la estructura del proyecto y depurar errores. Probé yo
mismo el flujo completo (registro, compra con Stripe e historial) y las correcciones del backend.