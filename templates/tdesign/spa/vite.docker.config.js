import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import { resolve } from 'path'

// Docker 控制台构建配置
export default defineConfig({
  plugins: [vue()],
  base: './',
  resolve: {
    alias: {
      '@': resolve(__dirname, 'src'),
    },
  },
  build: {
    outDir: resolve(__dirname, '../docker/dist'),
    emptyOutDir: true,
    assetsDir: 'assets',
    cssCodeSplit: false,
    chunkSizeWarningLimit: 3000,
    rollupOptions: {
      input: resolve(__dirname, 'docker.html'),
      output: {
        entryFileNames: 'assets/index.js',
        chunkFileNames: 'assets/[name].js',
        assetFileNames: (info) => {
          const n = info.name || ''
          if (n.endsWith('.css')) return 'assets/index.css'
          return 'assets/[name][extname]'
        },
        inlineDynamicImports: true,
      },
    },
  },
  server: {
    port: 5176,
    proxy: {
      '/docker': {
        target: 'http://127.0.0.1',
        changeOrigin: true,
      },
    },
  },
})
