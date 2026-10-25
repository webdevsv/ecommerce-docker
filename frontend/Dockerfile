# syntax=docker/dockerfile:1.7
# ============================================================
# ETAPA 1: base -> imagen común mínima
# ============================================================
FROM node:20-alpine AS base
RUN apk add --no-cache libc6-compat
WORKDIR /app
ENV NEXT_TELEMETRY_DISABLED=1

# ============================================================
# ETAPA 2: deps -> instala dependencias (capa cacheada)
# ============================================================
FROM base AS deps
COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund

# ============================================================
# ETAPA 3: builder -> compila la app (next build)
# ============================================================
FROM base AS builder
COPY --from=deps /app/node_modules ./node_modules
COPY . .
# Las variables NEXT_PUBLIC_* se incrustan en el JavaScript del navegador
# durante el build, por eso se pasan como ARG.
ARG NEXT_PUBLIC_STRIPE_PUBLISHABLE_KEY
ENV NEXT_PUBLIC_STRIPE_PUBLISHABLE_KEY=${NEXT_PUBLIC_STRIPE_PUBLISHABLE_KEY}
RUN npm run build

# ============================================================
# ETAPA 4: runner -> IMAGEN FINAL de producción
# ============================================================
FROM node:20-alpine AS runner
WORKDIR /app
ENV NODE_ENV=production \
    NEXT_TELEMETRY_DISABLED=1 \
    PORT=3000 \
    HOSTNAME=0.0.0.0

# Usuario sin privilegios
RUN addgroup -S -g 1001 nodejs && adduser -S -u 1001 -G nodejs nextjs

# Solo lo necesario para ejecutar la app
COPY --from=builder --chown=nextjs:nodejs /app/public ./public
COPY --from=builder --chown=nextjs:nodejs /app/.next/standalone ./
COPY --from=builder --chown=nextjs:nodejs /app/.next/static ./.next/static

USER nextjs
EXPOSE 3000

HEALTHCHECK --interval=30s --timeout=5s --start-period=20s --retries=3 \
  CMD wget -qO- http://127.0.0.1:3000/ >/dev/null 2>&1 || exit 1

CMD ["node", "server.js"]