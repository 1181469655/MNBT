import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import { resolve } from 'path'

// 主页/售卖前端构建配置（home scope）
export default defineConfig({
  plugins: [vue()],
  base: './',
  resolve: {
    alias: {
      '@': resolve(__dirname, 'src'),
    },
  },
  build: {
    outDir: resolve(__dirname, '../home/dist'),
    emptyOutDir: true,
    assetsDir: 'assets',
    cssCodeSplit: false,
    chunkSizeWarningLimit: 3000,
    rollupOptions: {
      input: resolve(__dirname, 'home.html'),
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
    port: 5177,
    proxy: {
      '/index.php': {
        target: 'http://127.0.0.1',
        changeOrigin: true,
      },
    },
  },
})
