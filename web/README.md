# Sentry Resolve Website

Static marketing site powered by [Vite](https://vitejs.dev/).

## Prerequisites

- Node.js 18+
- npm 9+

## Setup

Install dependencies from the `website` directory:

```bash
npm install
```

## Development

Start a hot-reloading dev server:

```bash
npm run dev
```

By default Vite listens on `http://localhost:5173`. Use `npm run dev -- --host` to expose the server to your network when testing on multiple devices.

## Build

Create an optimized production build:

```bash
npm run build
```

The output is placed in `dist/`. Deploy these files to your Plesk hosting (e.g., upload to the domain's document root).

## Preview

Serve the production build locally to verify before deploying:

```bash
npm run preview
```
